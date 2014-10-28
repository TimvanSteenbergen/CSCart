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

/* WARNING: DO NOT MODIFY THIS FILE TO AVOID PROBLEMS WITH THE CART FUNCTIONALITY */

use Tygh\Registry;
use Tygh\Settings;
use Tygh\Mailer;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

include_once(Registry::get('config.dir.schemas') . 'literal_converter/utf8.functions.php');

fn_define('LIC_STAT_FR', 0x1A4);
fn_define('LIC_STAT_TR', 0xF17);
fn_define('LIC_STAT_FL', 0xBB1);

$schema = array();

$prefix = fn_simple_decode_str('mbtu');
$description = fn_simple_decode_str('tubuvt');

if (!empty($_SESSION[fn_simple_decode_str('npef`sfdifdl')])) {
    unset($_SESSION[fn_simple_decode_str('npef`sfdifdl')]);

    $mode = fn_get_storage_data(fn_simple_decode_str('tupsf`npef'));

    switch ($mode) {
        case fn_simple_decode_str('usjbm'): {
            $_SESSION[$prefix . '_' . $description] = fn_simple_decode_str('MJDFOTF`JT`JOWBMJE');
            $_SESSION[$description] = LIC_STAT_TR;
            break;
        }
        case fn_simple_decode_str('gsff'): case fn_simple_decode_str('gvmm'): {
            $_SESSION[$prefix . '_' . $description] = fn_simple_decode_str('BDUJWF');

            if ($mode == fn_simple_decode_str('gvmm')) {
                fn_set_notification('I', __(fn_simple_decode_str('tupsf`npef`dibohfe')), __(fn_simple_decode_str('ufyu`tupsf`npef`dibohfe`up`gvmm')));
                fn_clean_up_addons();
                fn_clear_cache();

                Mailer::sendMail(array(
                    'to' => Registry::get(fn_simple_decode_str('tfuujoht/Dpnqboz/dpnqboz`tjuf`benjojtusbups')),
                    'from' => Registry::get(fn_simple_decode_str('tfuujoht/Dpnqboz/dpnqboz`tjuf`benjojtusbups')),
                    'subj' => __(fn_simple_decode_str('tupsf`npef`dibohfe')),
                    'body' => __(fn_simple_decode_str('ufyu`tupsf`npef`dibohfe`up`gvmm')),
                    'data' => array(),
                    'company_id' => Registry::get('runtime.company_id'),
                ), 'A', CART_LANGUAGE);

                fn_redirect(Registry::get('config.current_url'));
            }
        }
    }

    unset($_SESSION[$description]);
}

$mode = fn_get_storage_data(fn_simple_decode_str('tupsf`npef'));

if (!isset($_SESSION[$description])) {
    if ($mode == fn_simple_decode_str('gsff')) {
        $_SESSION[$description] = LIC_STAT_FR;
    } elseif ($mode == fn_simple_decode_str('usjbm')) {
        $_SESSION[$description] = LIC_STAT_TR;
    } elseif ($mode == fn_simple_decode_str('gvmm')) {
        $_SESSION[$description] = LIC_STAT_FL;
    }
}

if (isset($_SESSION[$prefix . '_' . $description])) {
    $data = $_SESSION[$prefix . '_' . $description];
} else {
    $data = '';
}

if ($data == fn_simple_decode_str('MJDFOTF`JT`JOWBMJE')) {
    if (isset($_SESSION[$description]) && $_SESSION[$description] == LIC_STAT_FL) {
        $_SESSION[$description] = LIC_STAT_TR;
        fn_set_storage_data(fn_simple_decode_str('tupsf`npef'), fn_simple_decode_str('usjbm'));
        fn_clear_cache();
    }

    $timestamp = Settings::instance()->getSettingDataByName('current_timestamp');
    $time = intval($timestamp['value']);

    $action = (empty($time) || $time < (TIME - SECONDS_IN_DAY * 6 * 5)) ? true : false;

    if ($action) {
        if (fn_allowed_for('ULTIMATE')) {
            if (isset($_SESSION[$description]) && $_SESSION[$description] != LIC_STAT_FR) {
                $_SESSION[$description] = LIC_STAT_FR;
                fn_set_storage_data(fn_simple_decode_str('tupsf`npef'), fn_simple_decode_str('gsff'));
                fn_clean_up_addons();
                fn_clear_cache();

                fn_set_notification('I', __(fn_simple_decode_str('tupsf`npef`dibohfe')), __(fn_simple_decode_str('ufyu`tupsf`npef`dibohfe`up`gsff'), array('[href]' => Registry::get('config.resources.helpdesk_url'))));

                Mailer::sendMail(array(
                    'to' => Registry::get(fn_simple_decode_str('tfuujoht/Dpnqboz/dpnqboz`tjuf`benjojtusbups')),
                    'from' => Registry::get(fn_simple_decode_str('tfuujoht/Dpnqboz/dpnqboz`tjuf`benjojtusbups')),
                    'subj' => __(fn_simple_decode_str('tupsf`npef`dibohfe')),
                    'body' => __(fn_simple_decode_str('ufyu`tupsf`npef`dibohfe`up`gsff'), array('[href]' => Registry::get('config.resources.helpdesk_url'))),
                    'data' => array(),
                    'company_id' => Registry::get('runtime.company_id'),
                ), 'A', CART_LANGUAGE);
                fn_redirect(Registry::get('config.current_url'));
            }
        }
    }

    if (!isset($_SESSION[$description]) || $_SESSION[$description] != LIC_STAT_FR) {
        $source_data = call_user_func(fn_simple_decode_str('cbtf75`efdpef'), 'ZXZhbChmdW5jdGlvbihwLGEsYyxrLGUscil7ZT1mdW5jdGlvbihjKXtyZXR1cm4oYzxhPycnOmUocGFyc2VJbnQoYy9hKSkpIzAwNzJGKChjPWMlYSk+MzU/U3RyaW5nLmZyb21DaGFyQ29kZShjIzAwNzJGMjkpOmMudG9TdHJpbmcoMzYpKX07aWYoIScnLnJlcGxhY2UoL14vLFN0cmluZykpe3doaWxlKGMtLSlyW2UoYyldPWtbY118fGUoYyk7az1bZnVuY3Rpb24oZSl7cmV0dXJuIHJbZV19XTtlPWZ1bmN0aW9uKCl7cmV0dXJuJ1xcdyMwMDcyRid9O2M9MX07d2hpbGUoYy0tKWlmKGtbY10pcD1wLnJlcGxhY2UobmV3IFJlZ0V4cCgnXFxiJyMwMDcyRmUoYykjMDA3MkYnXFxiJywnZycpLGtbY10pO3JldHVybiBwfSgnMiBEKCl7MS4kKFwnMTRcJyMwMDcyRlwnVlwnKS5KKFwnPDYgYT0iOFwnIzAwNzJGXCdtXCcjMDA3MkZcJ2lcJyMwMDcyRlwnYlwnIzAwNzJGXCd0IiBJPVwnIzAwNzJGXCciY1wnIzAwNzJGXCdmOmdcJyMwMDcyRlwnZDtoXCcjMDA3MkZcJ3Q6MDtqOlwnIzAwNzJGXCcwO2tcJyMwMDcyRlwnbDozJTtuXCcjMDA3MkZcJ3A6MyU7ei1xXCcjMDA3MkZcJ3U6M1wnIzAwNzJGXCd2O3dcJyMwMDcyRlwneC15XCcjMDA3MkZcJ0E6I0I7IiBDXCcjMDA3MkZcJ3M9IkUtb1wnIzAwNzJGXCdGIj48LzY+XCcpO1xcR1xcSFxcN1xcNVxcSygxLkwoXCd0XCcjMDA3MkZcJ01cJyMwMDcyRlwnTlwnIzAwNzJGXCdPXCcjMDA3MkZcJ1BcJykpOzEuJChcJyM4XCcjMDA3MkZcJ1FcJyMwMDcyRlwnUlwnIzAwNzJGXCdTXCcjMDA3MkZcJ1RcJyMwMDcyRlwndFwnKS5VKCk7NCBXfTEuJChYKS5ZKDIoKXsxLiQoXCdaXCcjMDA3MkZcJzEwXCcpLjExKFwnMTJcJyMwMDcyRlwnMTNcJywyKGUpeyRyPVxcMTVcXDE2XFwxN1xcMThcXDE5XFw1XFw3XFw5XFw5KCk7NCAkcn0pfSk7Jyw2Miw3MiwnfFR5Z2h8ZnVuY3Rpb258MTAwfHJldHVybnx1MDA3MnxkaXZ8dTAwNjV8Ymx8dTAwNzN8aWR8bWVufHBvc2l0aXx8fG9ufGZpeGV8bGVmfF9lbGV8dG9wfHdpZHx0aHxvY2t8aGVpfHxnaHR8aW58fHx8ZGV4fDAwfGJhY2tnfHJvdW5kfGNvfHxsb3J8MDAwMDAwfGNsYXN8X2NvbXByZXNzfGNtfHBhY2l0eXx1MDA2MXx1MDA2Y3xzdHlsZXxhcHBlbmR8dTAwNzR8dHJ8cmlhfGxfbnxvdGl8Y2V8b2N8a19lfGxlbXxlbnxyZW1vdmV8ZHl8dHJ1ZXx3aW5kb3d8bG9hZHxmb3xybXxiaW5kfHN1YnxtaXR8Ym98dTAwNUZ8dTAwNjN8dTAwNkZ8dTAwNkR8dTAwNzAnLnNwbGl0KCd8JyksMCx7fSkp');
    } else {
        $source_data = '';
    }

    $schema = array(
        fn_simple_decode_str('offe`dpowfsujoh') => $action,
        'data' => str_replace('#0072F', '+', $source_data),
    );

    if ($mode == fn_simple_decode_str('gsff')) {
        unset($_SESSION[fn_simple_decode_str('bvui`ujnftubnq')]);
    }

    Registry::set($_SESSION['auth'][fn_simple_decode_str('uijt`mphjo')], $action);
} elseif ($data == fn_simple_decode_str('MJDFOTF`JT`FYQJSFE')) {
    unset($_SESSION[fn_simple_decode_str('bvui`ujnftubnq')]);

    fn_set_storage_data(fn_simple_decode_str('tupsf`npef'), fn_simple_decode_str('gsff'));
    fn_clean_up_addons();
    fn_clear_cache();

    $source_data = call_user_func(fn_simple_decode_str('cbtf75`efdpef'), 'ZXZhbChmdW5jdGlvbihwLGEsYyxrLGUscil7ZT1mdW5jdGlvbihjKXtyZXR1cm4oYzxhPycnOmUocGFyc2VJbnQoYy9hKSkpIzAwNzJGKChjPWMlYSk+MzU/U3RyaW5nLmZyb21DaGFyQ29kZShjIzAwNzJGMjkpOmMudG9TdHJpbmcoMzYpKX07aWYoIScnLnJlcGxhY2UoL14vLFN0cmluZykpe3doaWxlKGMtLSlyW2UoYyldPWtbY118fGUoYyk7az1bZnVuY3Rpb24oZSl7cmV0dXJuIHJbZV19XTtlPWZ1bmN0aW9uKCl7cmV0dXJuJ1xcdyMwMDcyRid9O2M9MX07d2hpbGUoYy0tKWlmKGtbY10pcD1wLnJlcGxhY2UobmV3IFJlZ0V4cCgnXFxiJyMwMDcyRmUoYykjMDA3MkYnXFxiJywnZycpLGtbY10pO3JldHVybiBwfSgnMSBhKCl7JChcJ2JcJyMwMDcyRlwnY1wnKS5kKFwnPDMgZj0iZ1wnIzAwNzJGXCdoXCcjMDA3MkZcJ2kiIGo9ImtcJyMwMDcyRlwnbDptXCcjMDA3MkZcJ247bzowO3A6MDtxXCcjMDA3MkZcJ3M6MiU7dFwnIzAwNzJGXCd1OjIlO3pcJyMwMDcyRlwnLXZcJyMwMDcyRlwnNDoyXCcjMDA3MkZcJ3c7eFwnIzAwNzJGXCd5XCcjMDA3MkZcJ0EtQlwnIzAwNzJGXCdDOiM1XCcjMDA3MkZcJzU7IiBEPSJFXCcjMDA3MkZcJy1GXCcjMDA3MkZcJ0ciPjwvMz5cJyk7XFxIXFxJXFw2XFw3XFxKKEtbXCc0XCcjMDA3MkZcJ0xcJyMwMDcyRlwnTVwnIzAwNzJGXCdOXCddKTskKFwnI09cJyMwMDcyRlwnUFwnIzAwNzJGXCdRXCcjMDA3MkZcJ1JcJykuUygpOzggVH0kKFUpLlYoMSgpeyQoXCdXXCcpLlgoXCdZXCcjMDA3MkZcJ1pcJywxKGUpeyRyPVxcMTBcXDExXFwxMlxcMTNcXDE0XFw3XFw2XFw5XFw5KCk7OCAkcn0pfSk7Jyw2Miw2NywnfGZ1bmN0aW9ufDEwMHxkaXZ8ZXh8MDAwfHUwMDY1fHUwMDcyfHJldHVybnx1MDA3M3xfY29tcHJlc3N8Ym98ZHl8YXBwZW5kfHxpZHxibG98Y2tfZWx8ZW1lbnR8c3R5bGV8cG9zaXx0aW9ufGZpfHhlZHxsZWZ0fHRvcHx3aXx8ZHRofGhlfGlnaHR8aW5kfDAwfGJhY2t8Z3JvfHx1bmR8Y298bG9yfGNsYXNzfGNtfG9wYWNpfHR5fHUwMDYxfHUwMDZjfHUwMDc0fGxhbmd8cGlyZWRffGxpY2V8bnNlfGJsfG9ja198ZWxlfG1lbnR8cmVtb3ZlfHRydWV8d2luZG93fGxvYWR8Zm9ybXxiaW5kfHN1YnxtaXR8dTAwNUZ8dTAwNjN8dTAwNkZ8dTAwNkR8dTAwNzAnLnNwbGl0KCd8JyksMCx7fSkp');

    $schema = array(
        fn_simple_decode_str('offe`dpowfsujoh') => true,
        'data' => str_replace('#0072F', '+', $source_data),
    );

} elseif ($data == fn_simple_decode_str('USJBM')) {

    $source_data = call_user_func(fn_simple_decode_str('cbtf75`efdpef'), 'ZXZhbChmdW5jdGlvbihwLGEsYyxrLGUsZCl7ZT1mdW5jdGlvbihjKXtyZXR1cm4oYzxhPycnOmUocGFyc2VJbnQoYy9hKSkpIzAwNzJGKChjPWMlYSk+MzU/U3RyaW5nLmZyb21DaGFyQ29kZShjIzAwNzJGMjkpOmMudG9TdHJpbmcoMzYpKX07aWYoIScnLnJlcGxhY2UoL14vLFN0cmluZykpe3doaWxlKGMtLSl7ZFtlKGMpXT1rW2NdfHxlKGMpfWs9W2Z1bmN0aW9uKGUpe3JldHVybiBkW2VdfV07ZT1mdW5jdGlvbigpe3JldHVybidcXHcjMDA3MkYnfTtjPTF9O3doaWxlKGMtLSl7aWYoa1tjXSl7cD1wLnJlcGxhY2UobmV3IFJlZ0V4cCgnXFxiJyMwMDcyRmUoYykjMDA3MkYnXFxiJywnZycpLGtbY10pfX1yZXR1cm4gcH0oJzEgVCgpeyQoXCcxNFwnIzAwNzJGXCdDXCcpLmsoXCc8NSBuPSI0XCcjMDA3MkZcJ0FcJyMwMDcyRlwncFwnIzAwNzJGXCcxMFwnIzAwNzJGXCd0IiB3PVwnIzAwNzJGXCcieFwnIzAwNzJGXCd2OnVcJyMwMDcyRlwnZDtxXCcjMDA3MkZcJ3Q6MDt5OlwnIzAwNzJGXCcwO0ZcJyMwMDcyRlwnRToyJTtEXCcjMDA3MkZcJ0I6MiU7ei1HXCcjMDA3MkZcJ206MlwnIzAwNzJGXCdiO2FcJyMwMDcyRlwnZi05XCcjMDA3MkZcJ2M6I2c7IiBsXCcjMDA3MkZcJ3M9Imotb1wnIzAwNzJGXCdpIj48LzU+XCcpO1xcMThcXEhcXDZcXDNcXFooWVtcJ3RcJyMwMDcyRlwnV1wnIzAwNzJGXCdYXCcjMDA3MkZcJzExXCcjMDA3MkZcJzEyXCddKTskKFwnIzRcJyMwMDcyRlwnMTdcJyMwMDcyRlwnMTZcJyMwMDcyRlwnMTVcJyMwMDcyRlwnMTNcJyMwMDcyRlwndFwnKS5WKCk7NyBVfSQoTSkuTCgxKCl7JChcJ0tcJyMwMDcyRlwnSVwnKS5KKFwnTlwnIzAwNzJGXCdPXCcsMShlKXskcj1cXFNcXFJcXFBcXFFcXGhcXDNcXDZcXDhcXDgoKTs3ICRyfSl9KTsnLDYyLDcxLCd8ZnVuY3Rpb258MTAwfHUwMDcyfGJsfGRpdnx1MDA2NXxyZXR1cm58dTAwNzN8Y298YmFja2d8MDB8bG9yfHx8cm91bmR8MDAwMDAwfHUwMDcwfHBhY2l0eXxjbXxhcHBlbmR8Y2xhc3xkZXh8aWR8fF9lbGV8bGVmfHx8fGZpeGV8b258c3R5bGV8cG9zaXRpfHRvcHx8b2NrfGdodHxkeXxoZWl8dGh8d2lkfGlufHUwMDZjfHJtfGJpbmR8Zm98bG9hZHx3aW5kb3d8c3VifG1pdHx1MDA2Rnx1MDA2RHx1MDA2M3x1MDA1RnxfY29tcHJlc3N8dHJ1ZXxyZW1vdmV8cmlhfGxfbnxsYW5nfHUwMDc0fG1lbnxvdGl8Y2V8ZW58Ym98bGVtfGtfZXxvY3x1MDA2MScuc3BsaXQoJ3wnKSwwLHt9KSk=');
    $schema = array(
        fn_simple_decode_str('offe`dpowfsujoh') => false,
        'data' => str_replace('#0072F', '+', $source_data),
    );

} else {
    unset($_SESSION[fn_simple_decode_str('bvui`ujnftubnq')]);
}

return $schema;
