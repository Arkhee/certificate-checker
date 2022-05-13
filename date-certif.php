<?php
require_once(__DIR__."/init.php");
CertificateMonitor::checkSecurityKey();
CertificateMonitor::checkDomains();
CertificateMonitor::displayDomainsExpired();
CertificateMonitor::displayDomainsNotExpired();
CertificateMonitor::sendMail();