<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

namespace Tygh;

class Mailer extends \phpmailer
{
    private static $_mailer;

    public function SetLanguage($lang_type = 'en', $lang_path = "language/")
    {
        $lang_path = Registry::get('config.dir.lib') . 'other/phpmailer/' . $lang_path;

        return parent::SetLanguage($lang_type, $lang_path);
    }

    public function AddImageStringAttachment($string, $filename, $encoding = "base64", $type = "application/octet-stream")
    {
        // Append to $attachment array
        $cur = count($this->attachment);
        $this->attachment[$cur][0] = $string;
        $this->attachment[$cur][1] = $filename;
        $this->attachment[$cur][2] = $filename;
        $this->attachment[$cur][3] = $encoding;
        $this->attachment[$cur][4] = $type;
        $this->attachment[$cur][5] = true; // isString
        $this->attachment[$cur][6] = "inline";
        $this->attachment[$cur][7] = $filename;
    }

    public function RFCDate()
    {
        return date('r');
    }

    public static function sendMail($params, $area = AREA, $lang_code = CART_LANGUAGE)
    {
        if (empty($params['to']) || empty($params['from']) || (empty($params['tpl']) && empty($params['body']))) {
            return false;
        }

        fn_disable_live_editor_mode();

        $from = array(
            'email' => '',
            'name' => ''
        );
        $to = array();
        $reply_to = array();
        $cc = array();

        $mailer = self::instance(!empty($params['mailer_settings']) ? $params['mailer_settings'] : array());

        fn_set_hook('send_mail_pre', $mailer, $params, $area, $lang_code);

        $mailer->ClearReplyTos();
        $mailer->ClearCCs();
        $mailer->ClearAttachments();
        $mailer->Sender = '';

        $params['company_id'] = !empty($params['company_id']) ? $params['company_id'] : 0;
        $company_data = fn_get_company_placement_info($params['company_id'], $lang_code);

        foreach (array('reply_to', 'to', 'cc') as $way) {
            if (!empty($params[$way])) {
                if (!is_array($params[$way])) {
                    ${$way}[] = !empty($company_data[$params[$way]]) ? $company_data[$params[$way]] : $params[$way];
                } else {
                    foreach ($params[$way] as $way_ar) {
                        ${$way}[] = !empty($company_data[$way_ar]) ? $company_data[$way_ar] : $way_ar;
                    }
                }
            }

        }

        if (!empty($reply_to)) {
            $reply_to = $mailer->formatEmails($reply_to);
            foreach ($reply_to as $rep_to) {
                $mailer->AddReplyTo($rep_to);
            }
        }

        if (!empty($cc)) {
            $cc = $mailer->formatEmails($cc);
            foreach ($cc as $c) {
                $mailer->AddCC($c);
            }
        }

        if (!is_array($params['from'])) {
            if (!empty($company_data[$params['from']])) {
                $from['email'] =  $company_data[$params['from']];
                $from['name'] = strstr($params['from'], 'default_') ? $company_data['default_company_name'] : $company_data['company_name'];
            } elseif (self::ValidateAddress($params['from'])) {
                $from['email'] = $params['from'];
            }
        } else {
            if (!empty($params['from']['email'])) {

                if (!empty($company_data[$params['from']['email']])) {
                    $from['email'] =  $company_data[$params['from']['email']];
                    if (empty($params['from']['name'])) {
                        $params['from']['name'] = strstr($params['from']['email'], 'default_') ? $company_data['default_company_name'] : $company_data['company_name'];
                    }
                } else {
                    $from['email'] = $params['from']['email'];
                }
                $from['name'] = !empty($company_data[$params['from']['name']]) ? $company_data[$params['from']['name']] : $params['from']['name'];
            }
        }

        if (empty($to) || empty($from['email'])) {
            return false;
        }

        $mailer->SetFrom($from['email'], $from['name']);
        $mailer->IsHTML(isset($params['is_html']) ? $params['is_html'] : true);
        $mailer->CharSet = CHARSET;

        // Pass data to template
        foreach ($params['data'] as $k => $v) {
            Registry::get('view')->assign($k, $v);
        }
        Registry::get('view')->assign('company_data', $company_data);

        $company_id = isset($params['company_id']) ? $params['company_id'] : null;

        if (!empty($params['tpl'])) {
            // Get template name for subject and render it
            $tpl_ext = fn_get_file_ext($params['tpl']);
            $subj_tpl = str_replace('.' . $tpl_ext, '_subj.' . $tpl_ext, $params['tpl']);
            $subject = Registry::get('view')->displayMail($subj_tpl, false, $area, $company_id, $lang_code);

            // Render template for body
            $body = Registry::get('view')->displayMail($params['tpl'], false, $area, $company_id, $lang_code);

        } else {
            $subject = $params['subj'];
            $body = $params['body'];
        }

        $mailer->Body = $mailer->attachImages($body);
        $mailer->Subject = trim($subject);

        if (!empty($params['attachments'])) {
            foreach ($params['attachments'] as $name => $file) {
                $mailer->AddAttachment($file, $name);
            }
        }

        $to = $mailer->formatEmails($to);

        foreach ($to as $v) {
            $mailer->ClearAddresses();
            $mailer->AddAddress($v, '');
            $result = $mailer->Send();
            if (!$result) {
                fn_set_notification('E', __('error'), __('error_message_not_sent') . ' ' . $mailer->ErrorInfo);
            }

            fn_set_hook('send_mail', $mailer);
        }

        return $result;
    }

    private static function instance($mailer_settings = array())
    {
        static $default_settings;
        if (empty($default_settings)) {
            $default_settings = Settings::instance()->getValues('Emails');
        }

        if (empty($mailer_settings)) {
            $mailer_settings = $default_settings;
        }

        if (empty(self::$_mailer)) {
            self::$_mailer = new Mailer();
            self::$_mailer->LE = (defined('IS_WINDOWS')) ? "\r\n" : "\n";
            self::$_mailer->PluginDir = Registry::get('config.dir.lib') . 'other/phpmailer/';
        }

        if ($mailer_settings['mailer_send_method'] == 'smtp') {
            self::$_mailer->IsSMTP();
            self::$_mailer->SMTPAuth = ($mailer_settings['mailer_smtp_auth'] == 'Y') ? true : false;
            self::$_mailer->Host = $mailer_settings['mailer_smtp_host'];
            self::$_mailer->Username = $mailer_settings['mailer_smtp_username'];
            self::$_mailer->Password = $mailer_settings['mailer_smtp_password'];
            self::$_mailer->SMTPSecure = $mailer_settings['mailer_smtp_ecrypted_connection'];

        } elseif ($mailer_settings['mailer_send_method'] == 'sendmail') {
            self::$_mailer->IsSendmail();
            self::$_mailer->Sendmail = $mailer_settings['mailer_sendmail_path'];

        } else {
            self::$_mailer->IsMail();
        }

        return self::$_mailer;

    }

    public function attachImages($body)
    {
        $http_location = Registry::get('config.http_location');
        $https_location = Registry::get('config.https_location');
        $http_path = Registry::get('config.http_path');
        $https_path = Registry::get('config.https_path');
        $files = array();

        if (preg_match_all("/(?<=\ssrc=|\sbackground=)('|\")(.*)\\1/SsUi", $body, $matches)) {
            $files = fn_array_merge($files, $matches[2], false);
        }

        if (preg_match_all("/(?<=\sstyle=)('|\").*url\(('|\"|\\\\\\1)(.*)\\2\).*\\1/SsUi", $body, $matches)) {
            $files = fn_array_merge($files, $matches[3], false);
        }

        if (empty($files)) {
            return $body;
        } else {
            $files = array_unique($files);
            foreach ($files as $k => $_path) {
                $cid = 'csimg'.$k;
                $path = str_replace('&amp;', '&', $_path);

                $real_path = '';
                // Replace url path with filesystem if this url is NOT dynamic
                if (strpos($path, '?') === false && strpos($path, '&') === false) {
                    if (($i = strpos($path, $http_location)) !== false) {
                        $real_path = substr_replace($path, Registry::get('config.dir.root'), $i, strlen($http_location));
                    } elseif (($i = strpos($path, $https_location)) !== false) {
                        $real_path = substr_replace($path, Registry::get('config.dir.root'), $i, strlen($https_location));
                    } elseif (!empty($http_path) && ($i = strpos($path, $http_path)) !== false) {
                        $real_path = substr_replace($path, Registry::get('config.dir.root'), $i, strlen($http_path));
                    } elseif (!empty($https_path) && ($i = strpos($path, $https_path)) !== false) {
                        $real_path = substr_replace($path, Registry::get('config.dir.root'), $i, strlen($https_path));
                    }
                }

                if (empty($real_path)) {
                    $real_path = (strpos($path, '://') === false) ? $http_location .'/'. $path : $path;
                }

                list($width, $height, $mime_type) = fn_get_image_size($real_path);

                if (!empty($width)) {
                    $cid .= '.' . fn_get_image_extension($mime_type);
                    $content = fn_get_contents($real_path);
                    $this->AddImageStringAttachment($content, $cid, 'base64', $mime_type);
                    $body = preg_replace("/(['\"])" . str_replace("/", "\/", preg_quote($_path)) . "(['\"])/Ss", "\\1cid:" . $cid . "\\2", $body);
                }
            }
        }

        return $body;
    }

    public function formatEmails($emails)
    {
        $result = array();
        if (!is_array($emails)) {
            $emails = array($emails);
        }
        foreach ($emails as $email) {
            $email = str_replace(';', ',', $email);
            $res = explode(',', $email);
            foreach ($res as &$v) {
                $v = trim($v);
            }
            $result = array_merge($result, $res);
        }

        return array_unique($result);
    }
}
