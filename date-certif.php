<?php
require_once(__DIR__."/init.php");
CertificateMonitor::checkDomains();
CertificateMonitor::displayDomainsExpired();
CertificateMonitor::displayDomainsNotExpired();
CertificateMonitor::sendMail();