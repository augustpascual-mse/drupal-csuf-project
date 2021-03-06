<?php

/**
 * @file
 * Contains Drupal\MimeMail\Plugin\Mail\MimeMail.
 */

namespace Drupal\mimemail\Plugin\Mail;

use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Mail\MailFormatHelper;
use Drupal\mimemail\Utility\MimeMailFormatHelper;
use Drupal\Core\Mail\Plugin\Mail\PhpMail;
use Drupal\Core\Site\Settings;
use Drupal\Component\Utility\Unicode;

/**
 * Defines the default Drupal mail backend, using PHP's native mail() function.
 *
 * @Mail(
 *   id = "mime_mail",
 *   label = @Translation("Mime Mail mailer"),
 *   description = @Translation("Sends MIME-encoded emails with embedded images and attachments..")
 * )
 */
class MimeMail implements MailInterface {

  /**
   * {@inheritdoc}
   */
  public function format(array $message) {
    if (is_array($message['body'])) {
      $message['body'] = implode("\n\n", $message['body']);
    }

    if (preg_match('/plain/', $message['headers']['Content-Type'])) {
      if (!$format = \Drupal::config('mimemail.settings')->get('format')) {
        $format = filter_fallback_format();
      }
      $message['body'] = check_markup($message['body'], $format);
    }

    /*$engine = variable_get('mimemail_engine', 'mimemail');
    //$mailengine = $engine . '_mailengine';
    $engine_prepare_message = $engine . '_prepare_message';

    if (function_exists($engine_prepare_message)) {
      $message = $engine_prepare_message($message);
    }
    else {
      $message = mimemail_prepare_message($message);
    }*/
    // @TODO set mimemail_engine. For the moment let's prepare the message.
    $message =  $this->prepareMessage($message);

    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function mail(array $message) {
      // If 'Return-Path' isn't already set in php.ini, we pass it separately
      // as an additional parameter instead of in the header.
      if (isset($message['headers']['Return-Path'])) {
        $return_path_set = strpos(ini_get('sendmail_path'), ' -f');
        if (!$return_path_set) {
          $message['Return-Path'] = $message['headers']['Return-Path'];
          unset($message['headers']['Return-Path']);
        }
      }
      $mimeheaders = array();
      foreach ($message['headers'] as $name => $value) {
        $mimeheaders[] = $name . ': ' . Unicode::mimeHeaderEncode($value);
      }
      $line_endings = Settings::get('mail_line_endings', PHP_EOL);
      // Prepare mail commands.
      $mail_subject = Unicode::mimeHeaderEncode($message['subject']);
      // Note: email uses CRLF for line-endings. PHP's API requires LF
      // on Unix and CRLF on Windows. Drupal automatically guesses the
      // line-ending format appropriate for your system. If you need to
      // override this, adjust $settings['mail_line_endings'] in settings.php.
      $mail_body = preg_replace('@\r?\n@', $line_endings, $message['body']);
      // For headers, PHP's API suggests that we use CRLF normally,
      // but some MTAs incorrectly replace LF with CRLF. See #234403.
      $mail_headers = join("\n", $mimeheaders);

      $request = \Drupal::request();

      // We suppress warnings and notices from mail() because of issues on some
      // hosts. The return value of this method will still indicate whether mail
      // was sent successfully.
      if (!$request->server->has('WINDIR') && strpos($request->server->get('SERVER_SOFTWARE'), 'Win32') === FALSE) {
        // On most non-Windows systems, the "-f" option to the sendmail command
        // is used to set the Return-Path. There is no space between -f and
        // the value of the return path.
        $additional_headers = isset($message['Return-Path']) ? '-f' . $message['Return-Path'] : '';
        $mail_result = @mail(
          $message['to'],
          $mail_subject,
          $mail_body,
          $mail_headers,
          $additional_headers
        );
      }
      else {
        // On Windows, PHP will use the value of sendmail_from for the
        // Return-Path header.
        $old_from = ini_get('sendmail_from');
        ini_set('sendmail_from', $message['Return-Path']);
        $mail_result = @mail(
          $message['to'],
          $mail_subject,
          $mail_body,
          $mail_headers
        );
        ini_set('sendmail_from', $old_from);
      }

      return $mail_result;
  }

  /**
   * Prepares the message for sending.
   *
   * @param array $message
   *   An array containing the message data. The optional parameters are:
   *   - plain: Whether to send the message as plaintext only or HTML. If evaluates to TRUE,
   *     then the message will be sent as plaintext.
   *   - plaintext: Optional plaintext portion of a multipart email.
   *   - attachments: An array of arrays which describe one or more attachments.
   *     Existing files can be added by path, dinamically-generated files
   *     can be added by content. The internal array contains the following elements:
   *      - filepath: Relative Drupal path to an existing file (filecontent is NULL).
   *      - filecontent: The actual content of the file (filepath is NULL).
   *      - filename: The filename of the file.
   *      - filemime: The MIME type of the file.
   *      The array of arrays looks something like this:
   *      Array
   *      (
   *        [0] => Array
   *        (
   *         [filepath] => '/sites/default/files/attachment.txt'
   *         [filecontent] => 'My attachment.'
   *         [filename] => 'attachment.txt'
   *         [filemime] => 'text/plain'
   *        )
   *      )
   *
   * @return array
   *   All details of the message.
   */
  public function prepareMessage(array $message) {
    $module = $message['module'];
    $key = $message['key'];
    $to = $message['to'];
    $from = $message['from'];
    $subject = $message['subject'];
    $body = $message['body'];

    $headers = isset($message['params']['headers']) ? $message['params']['headers'] : array();
    $plain = isset($message['params']['plain']) ? $message['params']['plain'] : NULL;
    $plaintext = isset($message['params']['plaintext']) ? $message['params']['plaintext'] : NULL;
    $attachments = isset($message['params']['attachments']) ? $message['params']['attachments'] : array();

    $site_name = \Drupal::config('system.site')->get('name');
    //$site_mail = variable_get('site_mail', ini_get('sendmail_from'));
    $site_mail = \Drupal::config('system.site')->get('mail');
    /*$simple_address = variable_get('mimemail_simple_address', 0);*/

    // Override site mails default sender when using default engine.
    /*if ((empty($from) || $from == $site_mail)
      && variable_get('mimemail_engine', 'mimemail') == 'mimemail') {
      $mimemail_name = variable_get('mimemail_name', $site_name);
      $mimemail_mail = variable_get('mimemail_mail', $site_mail);
      $from = array(
        'name' => !empty($mimemail_name) ? $mimemail_name : $site_name,
        'mail' => !empty($mimemail_mail) ? $mimemail_mail : $site_mail,
      );
    }*/
    // Override site mails default sender when using default engine.
    if ((empty($from) || $from == $site_mail)) {
      $mimemail_name = \Drupal::config('mimemail.settings')->get('name');
      $mimemail_mail = \Drupal::config('mimemail.settings')->get('mail');
      $from = array(
        'name' => !empty($mimemail_name) ? $mimemail_name : $site_name,
        'mail' => !empty($mimemail_mail) ? $mimemail_mail : $site_mail,
      );
    }

    // Body is empty, this is a plaintext message.
    if (empty($body)) {
      $plain = TRUE;
    }
    // Try to determine recipient's text mail preference.
    elseif (is_null($plain)) {
      if (is_object($to) && isset($to->data['mimemail_textonly'])) {
        $plain = $to->data['mimemail_textonly'];
      }
      elseif (is_string($to) && \Drupal::service('email.validator')->isValid($to)) {
        if (is_object($account = user_load_by_mail($to)) && isset($account->data['mimemail_textonly'])) {
          $plain = $account->data['mimemail_textonly'];
          // Might as well pass the user object to the address function.
          $to = $account;
        }
      }
    }

    // Removing newline character introduced by _drupal_wrap_mail_line();
    $subject = str_replace(array("\n"), '', trim(MailFormatHelper::htmlToText($subject)));

    $hook = array(
      'mimemail_message__' . $key,
      'mimemail_message__' . $module .'__'. $key,
    );

    $body = [
      '#theme' => 'mimemail_messages',
      '#module' => $module,
      '#key' => $key,
      '#recipient' => $to,
      '#subject' => $subject,
      '#body' => $body
    ];

    $body = \Drupal::service('renderer')->render($body);

    /*foreach (module_implements('mail_post_process') as $module) {
      $function = $module . '_mail_post_process';
      $function($body, $key);
    }*/

    //$plain = $plain || variable_get('mimemail_textonly', 0);
    $from = MimeMailFormatHelper::mimeMailAddress($from);
    $mail = MimeMailFormatHelper::mimeMailHtmlBody($body, $subject, $plain, $plaintext, $attachments);
    $headers = array_merge($message['headers'], $headers, $mail['headers']);

    //$message['to'] = MimeMailFormatHelper::mimeMailAddress($to, $simple_address);
    $message['to'] = MimeMailFormatHelper::mimeMailAddress($to);
    $message['from'] = $from;
    $message['subject'] = $subject;
    $message['body'] = $mail['body'];
    $message['headers'] = MimeMailFormatHelper::mimeMailHeaders($headers, $from);

    return $message;
  }

}
