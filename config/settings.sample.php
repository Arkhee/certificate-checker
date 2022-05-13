<?php
class CertificateMonitorSettings
{
    static $mail_from="website@mail.com"; // From which email the alert is sent, check that the sending domain is authorized with your host IP (SPF etc)
    static $mail_to="your@mail.com"; // Who wille receive the alert
    static $sendmail=true; // Do you want to receive a mail alert ?
    static $lang="fr"; // Obviously because I'm french, set "en" or "fr", or provide another translation
    static $expiration_delay=30; // How many days left before alert
    static $log=true; // Log or not log ? that is the question
    static $log_file="date-certif.log"; // File name used for log, will be stored in root folder
    static $security_key="sdgerbjgesglibrtg456gdereggr";
}