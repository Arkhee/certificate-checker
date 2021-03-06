<?php

class CertificateMonitor
{
    static $translations=null;
    static $tableExpiration=array();
    static $arrListeDomainesSansDate=array();
    static $arrListeDomainesAvecDateExpiration=array();
    static $DNS=array();
    public static function loadTranslations()
    {
        if(is_null(self::$translations)) {
            self::$translations = array();
            if(file_exists(__ROOT_FOLDER__ . "/translations/" . CertificateMonitorSettings::$lang . ".php"))
            {
                include_once(__ROOT_FOLDER__ . "/translations/" . CertificateMonitorSettings::$lang . ".php");
            }
            if (isset($translations) && is_array($translations) && count($translations)) {
                self::$translations = $translations;
            }
        }
    }

    public static function checkSecurityKey()
    {
        $key=CertificateMonitorSettings::$security_key;
        if(!empty($key) && (!isset($_GET["key"]) || empty($_GET["key"]) || $_GET["key"]!==$key))
        {
            die("Incorrect security key");
        }
    }

    public static function getDelay()
    {
        return CertificateMonitorSettings::$expiration_delay;
    }

    public static function getDomains()
    {
        $domains=json_decode(file_get_contents(__ROOT_FOLDER__."/config/urls.json"));
        if(!is_array($domains)) die("Problem reading URLS config file");
        if(!count($domains)) die("No url provided to check certificate");
        return $domains;
    }

    public static function t($txt)
    {
        self::loadTranslations();
        return (isset(self::$translations[$txt])?self::$translations[$txt]:$txt);
    }

    public static function log($msg="")
    {
        if(CertificateMonitorSettings::$log)
        {
            file_put_contents(__DIR__."/date-certif.log",date("Y-m-d H:i:s")." => ".$msg."\r\n");
        }
    }

    public static function getDomainDates($domaine,&$dateDebut,&$dateFin)
    {
        // Cmd line without domains
        $cmd="echo '' | timeout 2 openssl s_client -servername ".$domaine." -connect ".$domaine.":443 2>/dev/null | openssl x509 -noout -dates";
        // Cmd line with domains
        $cmd="echo '' | timeout 2 openssl s_client -servername ".$domaine." -connect ".$domaine.":443 2>/dev/null | openssl x509 -noout -dates -text";
        $resultat=null;
        exec($cmd,$resultat);
        $output=self::t("Retour openssl")." : ".print_r($resultat,true)."\r\n";
        self::log(self::t("retour")." ".$output);
        self::$DNS[$domaine]="";
        if(is_array($resultat) && count($resultat)>0)
        {
            foreach($resultat as $curRes)
            {
                if(strpos($curRes,"notBefore=")!==false)
                {
                    $dateDebut=strtotime(str_replace("notBefore=","",$curRes));
                }
                if(strpos($curRes,"notAfter=")!==false)
                {
                    $dateFin=strtotime(str_replace("notAfter=","",$curRes));
                }
                if(strpos($curRes,"DNS:")!==false)
                {
                    self::$DNS[$domaine]=trim(str_replace("DNS:","",$curRes));
                }
            }
        }
        else
        {
            $output.=self::t("ERREUR timeout recuperation Certificat pour")." : ".$domaine."\r\n";
        }
        return $output;
    }

    public static function checkDomains()
    {

        $expirationTime=time()+self::getDelay()*24*3600;
        $output="";
        self::$tableExpiration=array();
        self::$arrListeDomainesAvecDateExpiration=array();
        self::$arrListeDomainesSansDate=array();
        $domaines=self::getDomains();
        self::log(self::t("D??but de la v??rification des certificats")." ".$output);
        foreach($domaines as $domaine)
        {
            self::log(self::t("test")." ".$domaine);
            $dateDebut="";
            $dateFin="";
            $output.=self::getDomainDates($domaine,$dateDebut,$dateFin);

            if(is_int($dateDebut) && is_int($dateFin))
            {
                self::$arrListeDomainesAvecDateExpiration[$dateFin][]=$domaine;
                $output.=self::t("Certificat pour")." : ".$domaine." => du ".date("d/m/Y",$dateDebut)." au ".date("d/m/Y",$dateFin)."\r\n";
            }
            else
            {
                self::$arrListeDomainesSansDate=array();
                $output.=self::t("Erreur r??cup??ration date pour")." ".$domaine." => ".self::t("d??but")." ".date($dateDebut)." ".self::t("fin")." ".($dateFin)."\r\n";
            }
            if(is_int($dateFin) && $dateFin<$expirationTime)
            {
                self::$tableExpiration[$domaine]=date("d/m/Y",$dateFin);
            }
        }
        ksort(self::$arrListeDomainesAvecDateExpiration);
        $output.="===========\r\n".self::t("Liste des domaines qui vont expirer")." : <pre>".print_r(self::$tableExpiration,true)."</pre>\r\n";
    }

    public static function displayDomainsNotExpired()
    {
        if(is_array(self::$arrListeDomainesSansDate) && count(self::$arrListeDomainesSansDate))
        {
            echo self::t("Liste des domaines sans date")." :<br>\r\n";
            echo "<pre>".print_r(self::$arrListeDomainesSansDate,true)."</pre>";
        }

    }

    public static function displayDomainsExpired()
    {
        if(is_array(self::$arrListeDomainesAvecDateExpiration) && count(self::$arrListeDomainesAvecDateExpiration))
        {
            echo "<h2>".self::t("Liste des domaines tri??s par date d'expiration")." :</h2>\r\n";
            echo "<table style='width:100%'><tr><th style='min-width:300px'>".self::t("Nom")."</th><th>".self::t("Date")."</th><th>DNS</th></tr>";
            foreach(self::$arrListeDomainesAvecDateExpiration as $keyDate => $listeDomaines)
            {
                foreach($listeDomaines as $domaine)
                {
                    echo "<tr><td>".$domaine . "</td><td>".date("d/m/Y",$keyDate)."</td><td>".(isset(self::$DNS[$domaine])?self::$DNS[$domaine]:"")."</td></tr>";
                }
            }
            echo "</table>";
        }
    }

    public static function sendMail()
    {
        if(CertificateMonitorSettings::$sendmail)
        {
            if(is_array(self::$tableExpiration) && count(self::$tableExpiration))
            {
                echo "\r\n<br />Envoi d'un mail";
                $to      = CertificateMonitorSettings::$mail_to;
                $subject = self::t("Des domaines vont expirer dans les 30 jours")." ".CertificateMonitorSettings::$expiration_delay." ".self::t("jours").' ('.count(self::$tableExpiration).')';
                $message = self::t("Les domaines suivants vont expirer dans les 30 jours")." ".CertificateMonitorSettings::$expiration_delay." ".self::t("jours").' :'."\r\n";
                $message .= print_r(self::$tableExpiration,true);
                $headers = 'From: '.CertificateMonitorSettings::$mail_from . "\r\n" .
                    'Reply-To: '.CertificateMonitorSettings::$mail_from . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();
                // $tmpfname = tempnam("/tmp", "MAILSCANCERTIF");
                //mail($to, $subject, $message, $headers);
                self::utf8mail($to,$subject,$message,CertificateMonitorSettings::$mail_from_name, CertificateMonitorSettings::$mail_from, CertificateMonitorSettings::$mail_from);
            }
        }
    }

    public static function utf8mail($to,$s,$body,$from_name="x",$from_a = "info@x.com", $reply="info@x.com")
    {
        $s= "=?utf-8?b?".base64_encode($s)."?=";
        $headers = "MIME-Version: 1.0\r\n";
        $headers.= "From: =?utf-8?b?".base64_encode($from_name)."?= <".$from_a.">\r\n";
        $headers.= "Content-Type: text/plain;charset=utf-8\r\n";
        $headers.= "Reply-To: $reply\r\n";
        $headers.= "X-Mailer: PHP/" . phpversion();
        mail($to, $s, $body, $headers);
    }
}