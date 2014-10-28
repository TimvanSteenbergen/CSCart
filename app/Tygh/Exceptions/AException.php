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

namespace Tygh\Exceptions;

use Tygh\Debugger;
use Tygh\Ajax;
use Tygh\Development;

abstract class AException extends \Exception
{
    /**
     * Outputs exception information
     * @return type
     */
    public function output()
    {
        if (!defined('AJAX_REQUEST') && Ajax::validateRequest($_REQUEST)) {
            // Return valid JS in ajax requests if the 'fail' status was thrown before ajax initialization
            header('Content-type: application/json');
            $message = json_encode(array(
                'error' => $this->message)
            );
            if (!empty($_REQUEST['callback'])) {
                $message = $_REQUEST['callback'] . "(" . $message . ");";
            }
            echo($message);
            exit;

        } elseif ((Debugger::isActive() || defined('DEVELOPMENT') || defined('CONSOLE'))) {
            echo($this->printDebug(defined('CONSOLE')));
        } else {
            $debug = "<!--\n" . $this->printDebug(true) . "\n-->";

            Development::showStub(array(
                '[title]' =>  'Service unavailable',
                '[banner]' =>  'Service<br/> unavailable',
                '[message]' => 'Sorry, service is temporarily unavailable.'
            ), $debug);
        }
    }

    /**
     * Prints out debug information
     * @param boolean $plain_text output as plain text
     */
    protected function printDebug($plain_text = false)
    {
        $file = str_replace(DIR_ROOT . '/', '', $this->file);

        $trace = <<< EOU
<h3>Message</h3>
{$this->message}

<h3>Error at</h3>
{$file}, line: {$this->line}

<h3>Backtrace</h3>
<table cellspacing='0' cellpadding='3'>
EOU;
        $i = 0;
        if ($backtrace = $this->getTrace()) {

            $func = '';
            foreach ($backtrace as $v) {
                if (empty($v['file'])) {
                    $func = $v['function'];
                    continue;
                } elseif (!empty($func)) {
                    $v['function'] = $func;
                    $func = '';
                }
                $i = ($i == 0) ? 1 : 0;
                $color = ($i == 0) ? "#CCCCCC" : "#EEEEEE";
                if (strpos($v['file'], DIR_ROOT) !== false) {
                    $v['file'] = str_replace(DIR_ROOT . '/', '', $v['file']);
                }

                $trace .= "<tr bgcolor='$color'><td>File:</td><td>$v[file]</td></tr>\n";
                $trace .= "<tr bgcolor='$color'><td>Line:</td><td>$v[line]</td></tr>\n";
                $trace .= "<tr bgcolor='$color'><td>Function:</td><td><b>$v[function]</b></td></tr>\n\n";
            }
        }

        $trace .= '</table>';

        if ($plain_text) {
            $trace = strip_tags($trace);
        }

        return $trace;
    }
}
