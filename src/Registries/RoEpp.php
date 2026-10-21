<?php

/*
 * Namingo EPP Client
 *
 * Copyright (c) 2023-2026 Taras Kondratyuk
 * Copyright (c) 2025-2026 Namingo contributors
 * Copyright (c) 2026 Terbora Ltd.
 *
 * Based in part on xpanel/epp-bundle
 * Copyright (c) 2017 Lilian Rudenco
 * https://github.com/xpanel/epp-bundle
 *
 * Licensed under the MIT License.
 * See the LICENSE file distributed with this software for the full license text.
 *
 * SPDX-License-Identifier: MIT
 */

namespace Pinga\Tembo\Registries;

use Pinga\Tembo\Epp;
use Pinga\Tembo\EppRegistryInterface;
use Pinga\Tembo\Exception\EppException;
use Pinga\Tembo\Exception\EppNotConnectedException;

class RoEpp extends Epp
{
    protected function addLoginObjects(\XMLWriter $xml): void
    {
        $xml->writeElement('objURI', 'http://www.rotld.ro/xml/epp/domain-1.0');
        $xml->writeElement('objURI', 'urn:ietf:params:xml:ns:contact-1.0');
        $xml->writeElement('objURI', 'urn:ietf:params:xml:ns:host-1.0');
    }

    protected function addLoginExtensions(\XMLWriter $xml): void
    {
        $xml->startElement('svcExtension');
        $xml->writeElement('extURI', 'http://www.rotld.ro/xml/epp/rotld-1.0');
        $xml->writeElement('extURI', 'http://www.rotld.ro/xml/epp/idn-1.0');
        $xml->writeElement('extURI', 'urn:ietf:params:xml:ns:secDNS-1.1');
        $xml->endElement(); // svcExtension
    }

    /**
     * contactCreate
     */
    public function contactCreate($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        $return = array();
        try {
            $additionalfields = (array)($params['additionalfields'] ?? array());
            $company = trim((string)($params['companyname'] ?? ''));
            $country = strtoupper(trim((string)($params['country'] ?? '')));
            $personType = strtolower(trim((string)($params['person_type'] ?? $additionalfields['Registrant Type'] ?? $additionalfields['Person Type'] ?? '')));
            $personTypes = array('p', 'ap', 'nc', 'c', 'gi', 'pi', 'o');
            $personLabels = array(
                'private person' => 'p',
                'authorized person' => 'ap',
                'non-commercial organization' => 'nc',
                'commercial' => 'c',
                'government institute' => 'gi',
                'public institute' => 'pi',
                'other juridical' => 'o'
            );
            if ($personType === '') {
                $personType = ($company !== '' ? 'c' : 'p');
            } else if (!in_array($personType, $personTypes, true)) {
                foreach ($personLabels as $label => $code) {
                    if (strpos($personType, $label) === 0) {
                        $personType = $code;
                        break;
                    }
                }
                if (!in_array($personType, $personTypes, true)) {
                    throw new EppException('RoTLD: invalid person_type. Use p, ap, nc, c, gi, pi or o.');
                }
            }
            $cnpFiscalCode = trim((string)($params['cnp_fiscal_code'] ?? $additionalfields['CNPFiscalCode'] ?? $additionalfields['CNP / Fiscal Code'] ?? ''));
            $registrationNumber = trim((string)($params['registration_number'] ?? $additionalfields['Registration Number'] ?? ''));
            if ($country === 'RO' && $cnpFiscalCode === '') {
                throw new EppException('RoTLD: CNP / Fiscal Code is mandatory for Romanian registrants.');
            }
            if ($country === 'RO' && $personType === 'c' && $registrationNumber === '') {
                throw new EppException('RoTLD: Registration Number is mandatory for Romanian companies.');
            }
            $name = trim(trim((string)($params['firstname'] ?? '')) . ' ' . trim((string)($params['lastname'] ?? '')));
            if (!in_array($personType, array('p', 'ap'), true) && $company !== '') {
                $name = $company;
            }
            $state = trim((string)($params['state'] ?? ''));
            if ($state === '') {
                $state = (string)($params['city'] ?? '');
            }

            $from = $to = array();
            $from[] = '/{{ type }}/';
            $to[] = 'int';
            $from[] = '/{{ id }}/';
            $to[] = 'AUTO';
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($name);
            $from[] = '/{{ org }}/';
            $to[] = htmlspecialchars($company);
            $from[] = '/{{ street1 }}/';
            $to[] = htmlspecialchars($params['address1'] ?? '');
            $from[] = '/{{ street2 }}/';
            $to[] = htmlspecialchars($params['address2'] ?? '');
            $from[] = '/{{ street3 }}/';
            $street3 = (isset($params['address3']) ? $params['address3'] : '');
            $to[] = htmlspecialchars($street3);
            $from[] = '/{{ city }}/';
            $to[] = htmlspecialchars($params['city'] ?? '');
            $from[] = '/{{ state }}/';
            $to[] = htmlspecialchars($state);
            $from[] = '/{{ postcode }}/';
            $to[] = htmlspecialchars($params['postcode'] ?? '');
            $from[] = '/{{ country }}/';
            $to[] = htmlspecialchars($country);
            $from[] = '/{{ phonenumber }}/';
            $to[] = htmlspecialchars($params['fullphonenumber'] ?? '');
            $from[] = '/{{ email }}/';
            $to[] = htmlspecialchars($params['email'] ?? '');
            $from[] = '/{{ authInfo }}/';
            $to[] = htmlspecialchars($params['authInfoPw'] ?? '');
            $from[] = '/{{ cnp_fiscal_code }}/';
            $to[] = htmlspecialchars($cnpFiscalCode);
            $from[] = '/{{ registration_number }}/';
            $to[] = htmlspecialchars($registrationNumber);
            $from[] = '/{{ person_type }}/';
            $to[] = htmlspecialchars($personType);
            $from[] = '/{{ clTRID }}/';
            $microtime = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-contact-create-' . $microtime);
            // RoTLD requires org and pw elements even when their values are empty.
            $from[] = "/<(?!contact:org>|contact:pw>)\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            foreach ($to as $key => $value) {
                $to[$key] = str_replace(array('\\', '$'), array('\\\\', '\\$'), (string)$value);
            }
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <create>
      <contact:create
       xmlns:contact="urn:ietf:params:xml:ns:contact-1.0">
        <contact:id>{{ id }}</contact:id>
        <contact:postalInfo type="{{ type }}">
          <contact:name>{{ name }}</contact:name>
          <contact:org>{{ org }}</contact:org>
          <contact:addr>
            <contact:street>{{ street1 }}</contact:street>
            <contact:street>{{ street2 }}</contact:street>
            <contact:street>{{ street3 }}</contact:street>
            <contact:city>{{ city }}</contact:city>
            <contact:sp>{{ state }}</contact:sp>
            <contact:pc>{{ postcode }}</contact:pc>
            <contact:cc>{{ country }}</contact:cc>
          </contact:addr>
        </contact:postalInfo>
        <contact:voice>{{ phonenumber }}</contact:voice>
        <contact:fax></contact:fax>
        <contact:email>{{ email }}</contact:email>
        <contact:authInfo>
          <contact:pw>{{ authInfo }}</contact:pw>
        </contact:authInfo>
      </contact:create>
    </create>
    <extension>
      <rotld:ext xmlns:rotld="http://www.rotld.ro/xml/epp/rotld-1.0">
        <rotld:create>
          <rotld:contact>
            <rotld:cnp_fiscal_code>{{ cnp_fiscal_code }}</rotld:cnp_fiscal_code>
            <rotld:registration_number>{{ registration_number }}</rotld:registration_number>
            <rotld:person_type>{{ person_type }}</rotld:person_type>
          </rotld:contact>
        </rotld:create>
      </rotld:ext>
    </extension>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('urn:ietf:params:xml:ns:contact-1.0')->creData;
            $id = (string)$r->id;

            $return = array(
                'code' => $code,
                'msg' => $msg,
                'id' => $id
            );
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
    }

    /**
     * domainCheck
     */
    public function domainCheck($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        $return = array();
        try {
            $from = $to = array();
            $text = '';
            foreach ($params['domains'] as $name) {
                $text .= '<domain:name>' . htmlspecialchars($name) . '</domain:name>' . "\n";
            }
            $from[] = '/{{ names }}/';
            $to[] = $text;
            $from[] = '/{{ clTRID }}/';
            $microtime = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-check-' . $microtime);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            foreach ($to as $key => $value) {
                $to[$key] = str_replace(array('\\', '$'), array('\\\\', '\\$'), (string)$value);
            }
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <check>
      <domain:check
        xmlns:domain="http://www.rotld.ro/xml/epp/domain-1.0"
        xsi:schemaLocation="http://www.rotld.ro/xml/epp/domain-1.0 domain-1.0.xsd">
        {{ names }}
      </domain:check>
    </check>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('http://www.rotld.ro/xml/epp/domain-1.0')->chkData;

            $domains = array();
            $i = 0;
            foreach ($r->cd as $cd) {
                $domains[$i]['name'] = (string)$cd->name;
                $availStr = (string)$cd->name->attributes()->avail;
                $domains[$i]['avail'] = ($availStr === 'true' || $availStr === '1') ? true : false;
                $domains[$i]['reason'] = (string)$cd->reason;
                $i++;
            }

            $return = array(
                'code' => $code,
                'msg' => $msg,
                'domains' => $domains
            );
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
    }

    /**
     * domainCheckClaims
     */
    public function domainCheckClaims($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        throw new EppException('Launch extension not supported!');
    }

    /**
     * domainCheckFee
     */
    public function domainCheckFee($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        throw new EppException('Fee extension not supported!');
    }

    /**
     * domainInfo
     */
    public function domainInfo($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        $return = array();
        try {
            $from = $to = array();
            $from[] = '/{{ domainname }}/';
            $to[] = htmlspecialchars($params['domainname']);
            $from[] = '/{{ authInfo }}/';
            $authInfo = (isset($params['authInfoPw']) ? "<domain:authInfo>\n<domain:pw><![CDATA[{$params['authInfoPw']}]]></domain:pw>\n</domain:authInfo>" : '');
            $to[] = $authInfo;
            $from[] = '/{{ clTRID }}/';
            $microtime = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-info-' . $microtime);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            foreach ($to as $key => $value) {
                $to[$key] = str_replace(array('\\', '$'), array('\\\\', '\\$'), (string)$value);
            }
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <info>
      <domain:info
       xmlns:domain="http://www.rotld.ro/xml/epp/domain-1.0"
       xsi:schemaLocation="http://www.rotld.ro/xml/epp/domain-1.0 domain-1.0.xsd">
        <domain:name hosts="all">{{ domainname }}</domain:name>
        {{ authInfo }}
      </domain:info>
    </info>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $resp = $this->writeRequest($xml);
            $code = (int)$resp->response->result->attributes()->code;
            $msg  = (string)$resp->response->result->msg;

            $r = $resp->response->resData->children('http://www.rotld.ro/xml/epp/domain-1.0')->infData;
            $name = (string)$r->name;
            $roid = (string)$r->roid;
            $status = array();
            $i = 0;
            foreach ($r->status as $e) {
                $i++;
                $status[$i] = (string)$e->attributes()->s;
            }
            $registrant = (string)$r->registrant;
            $contact = array();
            $i = 0;
            foreach ($r->contact as $e) {
                $i++;
                $contact[$i]['type'] = (string)$e->attributes()->type;
                $contact[$i]['id'] = (string)$e;
            }
            $ns = array();
            $i = 0;
            foreach (($r->ns->hostObj ?? []) as $hostObj) {
                $i++;
                $ns[$i] = (string)$hostObj;
            }
            $host = array();
            $i = 0;
            foreach ($r->host as $hostname) {
                $i++;
                $host[$i] = (string)$hostname;
            }
            $clID = (string)$r->clID;
            $crID = (string)$r->crID;
            $crDate = (string)$r->crDate;
            $upID = (string)$r->upID;
            $upDate = (string)$r->upDate;
            $exDate = (string)$r->exDate;
            $trDate = (string)$r->trDate;
            $authInfo = (string)$r->authInfo->pw;

            $dsData = [];
            $keyData = [];

            $ext = $resp->response->extension ?? null;

            if ($ext) {
                $sec = $ext->children('urn:ietf:params:xml:ns:secDNS-1.1')->infData ?? null;

                if ($sec) {
                    foreach ($sec->dsData ?? [] as $ds) {
                        $dsData[] = [
                            'keyTag'     => (int)$ds->keyTag,
                            'alg'        => (int)$ds->alg,
                            'digestType' => (int)$ds->digestType,
                            'digest'     => (string)$ds->digest,
                        ];
                    }

                    foreach ($sec->keyData ?? [] as $kd) {
                        $keyData[] = [
                            'flags'     => (int)$kd->flags,
                            'protocol'  => (int)$kd->protocol,
                            'alg'       => (int)$kd->alg,
                            'pubKey'    => (string)$kd->pubKey,
                        ];
                    }
                }
            }

            $return = array(
                'code' => $code,
                'msg' => $msg,
                'name' => $name,
                'roid' => $roid,
                'status' => $status,
                'registrant' => $registrant,
                'contact' => $contact,
                'ns' => $ns,
                'host' => $host,
                'clID' => $clID,
                'crID' => $crID,
                'crDate' => $crDate,
                'upID' => $upID,
                'upDate' => $upDate,
                'exDate' => $exDate,
                'trDate' => $trDate,
                'authInfo' => $authInfo
            );

            if (!empty($dsData)) {
                $return['dsData'] = $dsData;
            }

            if (!empty($keyData)) {
                $return['keyData'] = $keyData;
            }
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
    }

    /**
     * domainCreate
     */
    public function domainCreate($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        $return = array();
        try {
            $pw = (string)($params['authInfoPw'] ?? '');
            $ok = preg_match('/^[a-z0-9\-\.\,\:\;\[\]\{\}\_\+\=\@\#\$\^\*\?\!\|\~]{6,40}\z/i', $pw)
                && preg_match('/[A-Z]/', $pw) && preg_match('/[a-z]/', $pw)
                && preg_match('/[0-9]/', $pw) && preg_match('/[^A-Za-z0-9]/', $pw);
            if (!$ok) {
                throw new EppException('RoTLD: authInfoPw must contain 6-40 allowed characters, including uppercase, lowercase, a digit and a special character.');
            }
            $period = filter_var($params['period'] ?? 1, FILTER_VALIDATE_INT);
            if ($period === false || $period < 1 || $period > 10) {
                throw new EppException('RoTLD: period must be an integer from 1 to 10 years.');
            }
            if (empty($params['registrant'])) {
                throw new EppException('RoTLD: registrant contact ID is required.');
            }

            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            $from[] = '/{{ period }}/';
            $to[] = $period;

            $nameservers = array();
            if (!empty($params['nss']) && is_array($params['nss'])) {
                $nameservers = $params['nss'];
            } else {
                foreach ($params as $key => $value) {
                    if ($value && preg_match('/^ns\d$/i', (string)$key)) {
                        $nameservers[] = (string)$value;
                    }
                }
            }
            if (count($nameservers) > 6) {
                throw new EppException('RoTLD: at most six nameservers are supported.');
            }
            $hostAttr = is_array(reset($nameservers));
            $text = '';
            foreach ($nameservers as $hostObj) {
                if (is_array($hostObj) !== $hostAttr) {
                    throw new EppException('RoTLD: do not mix hostObj strings and hostAttr arrays.');
                }
                if (!is_array($hostObj)) {
                    $text .= '<domain:hostObj>' . htmlspecialchars($hostObj) . '</domain:hostObj>' . "\n";
                    continue;
                }

                $text .= "<domain:hostAttr>\n";
                $text .= '  <domain:hostName>' . htmlspecialchars($hostObj['hostName'] ?? '') . "</domain:hostName>\n";
                if (!empty($hostObj['ipv4'])) {
                    $text .= '  <domain:hostAddr ip="v4">' . htmlspecialchars($hostObj['ipv4']) . "</domain:hostAddr>\n";
                }
                if (!empty($hostObj['ipv6'])) {
                    $text .= '  <domain:hostAddr ip="v6">' . htmlspecialchars($hostObj['ipv6']) . "</domain:hostAddr>\n";
                }
                $text .= "</domain:hostAttr>\n";
            }
            $from[] = '/{{ hostObjs }}/';
            $to[]   = $text;

            $from[] = '/{{ registrant }}/';
            $to[]   = isset($params['registrant']) ? htmlspecialchars($params['registrant']): '';
            $from[] = '/{{ authInfoPw }}/';
            $to[] = htmlspecialchars($params['authInfoPw']);
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-create-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            foreach ($to as $key => $value) {
                $to[$key] = str_replace(array('\\', '$'), array('\\\\', '\\$'), (string)$value);
            }
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <create>
      <domain:create
       xmlns:domain="http://www.rotld.ro/xml/epp/domain-1.0">
        <domain:name>{{ name }}</domain:name>
        <domain:period unit="y">{{ period }}</domain:period>
        <domain:ns>
          {{ hostObjs }}
        </domain:ns>
        <domain:registrant>{{ registrant }}</domain:registrant>
        <domain:authInfo>
          <domain:pw>{{ authInfoPw }}</domain:pw>
        </domain:authInfo>
      </domain:create>
    </create>
    <extension>
      <rotld:ext xmlns:rotld="http://www.rotld.ro/xml/epp/rotld-1.0">
        <rotld:create>
          <rotld:domain>
            <rotld:agreement legal_use="yes" registration_rules="yes"/>
          </rotld:domain>
        </rotld:create>
      </rotld:ext>
    </extension>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('http://www.rotld.ro/xml/epp/domain-1.0')->creData;
            $name = (string)$r->name;
            $crDate = (string)$r->crDate;
            $exDate = (string)$r->exDate;

            $return = array(
                'code' => $code,
                'msg' => $msg,
                'name' => $name,
                'crDate' => $crDate,
                'exDate' => $exDate
            );
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
    }

    /**
     * domainCreateDNSSEC
     */
    public function domainCreateDNSSEC($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        $return = array();
        try {
            $pw = (string)($params['authInfoPw'] ?? '');
            $ok = preg_match('/^[a-z0-9\-\.\,\:\;\[\]\{\}\_\+\=\@\#\$\^\*\?\!\|\~]{6,40}\z/i', $pw)
                && preg_match('/[A-Z]/', $pw) && preg_match('/[a-z]/', $pw)
                && preg_match('/[0-9]/', $pw) && preg_match('/[^A-Za-z0-9]/', $pw);
            if (!$ok) {
                throw new EppException('RoTLD: authInfoPw must contain 6-40 allowed characters, including uppercase, lowercase, a digit and a special character.');
            }
            $period = filter_var($params['period'] ?? 1, FILTER_VALIDATE_INT);
            if ($period === false || $period < 1 || $period > 10) {
                throw new EppException('RoTLD: period must be an integer from 1 to 10 years.');
            }
            if (empty($params['registrant'])) {
                throw new EppException('RoTLD: registrant contact ID is required.');
            }

            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            $from[] = '/{{ period }}/';
            $to[] = $period;

            $nameservers = array();
            if (!empty($params['nss']) && is_array($params['nss'])) {
                $nameservers = $params['nss'];
            } else {
                foreach ($params as $key => $value) {
                    if ($value && preg_match('/^ns\d$/i', (string)$key)) {
                        $nameservers[] = (string)$value;
                    }
                }
            }
            if (count($nameservers) > 6) {
                throw new EppException('RoTLD: at most six nameservers are supported.');
            }
            $hostAttr = is_array(reset($nameservers));
            $text = '';
            foreach ($nameservers as $hostObj) {
                if (is_array($hostObj) !== $hostAttr) {
                    throw new EppException('RoTLD: do not mix hostObj strings and hostAttr arrays.');
                }
                if (!is_array($hostObj)) {
                    $text .= '<domain:hostObj>' . htmlspecialchars($hostObj) . '</domain:hostObj>' . "\n";
                    continue;
                }

                $text .= "<domain:hostAttr>\n";
                $text .= '  <domain:hostName>' . htmlspecialchars($hostObj['hostName'] ?? '') . "</domain:hostName>\n";
                if (!empty($hostObj['ipv4'])) {
                    $text .= '  <domain:hostAddr ip="v4">' . htmlspecialchars($hostObj['ipv4']) . "</domain:hostAddr>\n";
                }
                if (!empty($hostObj['ipv6'])) {
                    $text .= '  <domain:hostAddr ip="v6">' . htmlspecialchars($hostObj['ipv6']) . "</domain:hostAddr>\n";
                }
                $text .= "</domain:hostAttr>\n";
            }
            $from[] = '/{{ hostObjs }}/';
            $to[]   = $text;

            $from[] = '/{{ registrant }}/';
            $to[]   = isset($params['registrant']) ? htmlspecialchars($params['registrant']): '';
            $from[] = '/{{ authInfoPw }}/';
            $to[] = htmlspecialchars($params['authInfoPw']);
            $records = filter_var($params['dnssec_records'] ?? null, FILTER_VALIDATE_INT);
            if (!in_array($records, array(1, 2), true)) {
                throw new EppException('RoTLD: dnssec_records must be 1 or 2, as in the base client.');
            }
            for ($i = 1; $i <= $records; $i++) {
                foreach (array('keyTag', 'alg', 'digestType', 'digest') as $field) {
                    if (!isset($params[$field . '_' . $i]) || $params[$field . '_' . $i] === '') {
                        throw new EppException('RoTLD: missing DNSSEC field ' . $field . '_' . $i . '.');
                    }
                }
            }
            if ($records == 1) {
                $from[] = '/{{ dnssec_data }}/';
                $to[] = "<secDNS:dsData>
            <secDNS:keyTag>".htmlspecialchars($params['keyTag_1'])."</secDNS:keyTag>
            <secDNS:alg>".htmlspecialchars($params['alg_1'])."</secDNS:alg>
            <secDNS:digestType>".htmlspecialchars($params['digestType_1'])."</secDNS:digestType>
            <secDNS:digest>".htmlspecialchars($params['digest_1'])."</secDNS:digest>
          </secDNS:dsData>";
            } else if ($records == 2) {
                $from[] = '/{{ dnssec_data }}/';
                $to[] = "<secDNS:dsData>
            <secDNS:keyTag>".htmlspecialchars($params['keyTag_1'])."</secDNS:keyTag>
            <secDNS:alg>".htmlspecialchars($params['alg_1'])."</secDNS:alg>
            <secDNS:digestType>".htmlspecialchars($params['digestType_1'])."</secDNS:digestType>
            <secDNS:digest>".htmlspecialchars($params['digest_1'])."</secDNS:digest>
          </secDNS:dsData>
          <secDNS:dsData>
            <secDNS:keyTag>".htmlspecialchars($params['keyTag_2'])."</secDNS:keyTag>
            <secDNS:alg>".htmlspecialchars($params['alg_2'])."</secDNS:alg>
            <secDNS:digestType>".htmlspecialchars($params['digestType_2'])."</secDNS:digestType>
            <secDNS:digest>".htmlspecialchars($params['digest_2'])."</secDNS:digest>
          </secDNS:dsData>";
            }
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-createDNSSEC-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            foreach ($to as $key => $value) {
                $to[$key] = str_replace(array('\\', '$'), array('\\\\', '\\$'), (string)$value);
            }
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <create>
      <domain:create
       xmlns:domain="http://www.rotld.ro/xml/epp/domain-1.0">
        <domain:name>{{ name }}</domain:name>
        <domain:period unit="y">{{ period }}</domain:period>
        <domain:ns>
          {{ hostObjs }}
        </domain:ns>
        <domain:registrant>{{ registrant }}</domain:registrant>
        <domain:authInfo>
          <domain:pw>{{ authInfoPw }}</domain:pw>
        </domain:authInfo>
      </domain:create>
    </create>
    <extension>
      <rotld:ext xmlns:rotld="http://www.rotld.ro/xml/epp/rotld-1.0">
        <rotld:create>
          <rotld:domain>
            <rotld:agreement legal_use="yes" registration_rules="yes"/>
          </rotld:domain>
        </rotld:create>
      </rotld:ext>
      <secDNS:create xmlns:secDNS="urn:ietf:params:xml:ns:secDNS-1.1">
        {{ dnssec_data }}
      </secDNS:create>
    </extension>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('http://www.rotld.ro/xml/epp/domain-1.0')->creData;
            $name = (string)$r->name;
            $crDate = (string)$r->crDate;
            $exDate = (string)$r->exDate;

            $return = array(
                'code' => $code,
                'msg' => $msg,
                'name' => $name,
                'crDate' => $crDate,
                'exDate' => $exDate
            );
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
    }

    /**
     * domainCreateClaims
     */
    public function domainCreateClaims($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        throw new EppException('Launch extension not supported!');
    }

    /**
     * domainCreateSunrise
     */
    public function domainCreateSunrise($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        throw new EppException('Launch extension not supported!');
    }

    /**
     * domainUpdateNS
     */
    public function domainUpdateNS($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        $return = array();
        try {
            $nameservers = array();
            if (!empty($params['nss']) && is_array($params['nss'])) {
                $nameservers = $params['nss'];
            } else {
                foreach ($params as $key => $value) {
                    if ($value && preg_match('/^ns\d$/i', (string)$key)) {
                        $nameservers[] = (string)$value;
                    }
                }
            }
            if (count($nameservers) > 6) {
                throw new EppException('RoTLD: at most six nameservers are supported.');
            }
            $hostAttr = is_array(reset($nameservers));
            $text = '';
            foreach ($nameservers as $hostObj) {
                if (is_array($hostObj) !== $hostAttr) {
                    throw new EppException('RoTLD: do not mix hostObj strings and hostAttr arrays.');
                }
                if (!is_array($hostObj)) {
                    $text .= '<domain:hostObj>' . htmlspecialchars($hostObj) . '</domain:hostObj>' . "\n";
                    continue;
                }

                $text .= "<domain:hostAttr>\n";
                $text .= '  <domain:hostName>' . htmlspecialchars($hostObj['hostName'] ?? '') . "</domain:hostName>\n";
                if (!empty($hostObj['ipv4'])) {
                    $text .= '  <domain:hostAddr ip="v4">' . htmlspecialchars($hostObj['ipv4']) . "</domain:hostAddr>\n";
                }
                if (!empty($hostObj['ipv6'])) {
                    $text .= '  <domain:hostAddr ip="v6">' . htmlspecialchars($hostObj['ipv6']) . "</domain:hostAddr>\n";
                }
                $text .= "</domain:hostAttr>\n";
            }
            if ($text === '') {
                throw new EppException('RoTLD: refusing to send an empty nameserver set (it would detach all nameservers).');
            }

            // RoTLD replaces the entire nameserver set through domain:add.
            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            $from[] = '/{{ hostObjs }}/';
            $to[] = $text;
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-updateNS-' . $clTRID);
            foreach ($to as $key => $value) {
                $to[$key] = str_replace(array('\\', '$'), array('\\\\', '\\$'), (string)$value);
            }
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <update>
      <domain:update
       xmlns:domain="http://www.rotld.ro/xml/epp/domain-1.0"
       xsi:schemaLocation="http://www.rotld.ro/xml/epp/domain-1.0 domain-1.0.xsd">
        <domain:name>{{ name }}</domain:name>
        <domain:add>
          <domain:ns>
            {{ hostObjs }}
          </domain:ns>
        </domain:add>
      </domain:update>
    </update>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;

            $return = array(
                'code' => $code,
                'msg' => $msg
            );
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
    }

    /**
     * domainUpdateContact
     */
    public function domainUpdateContact($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        throw new EppException('RoTLD registrant changes require the right-of-use transfer procedure; domainUpdateContact is not implemented in this profile.');
    }

    /**
     * domainUpdateStatus
     */
    public function domainUpdateStatus($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        $return = array();
        try {
            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            if ($params['command'] === 'add') {
            $from[] = '/{{ add }}/';
            $to[] = "<domain:add><domain:status s=\"".htmlspecialchars($params['status'])."\"/></domain:add>\n";
            $from[] = '/{{ rem }}/';
            $to[] = "";
            } else if ($params['command'] === 'rem') {
            $from[] = '/{{ add }}/';
            $to[] = "";
            $from[] = '/{{ rem }}/';
            $to[] = "<domain:rem><domain:status s=\"".htmlspecialchars($params['status'])."\"/></domain:rem>\n";
            }
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-updateStatus-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            foreach ($to as $key => $value) {
                $to[$key] = str_replace(array('\\', '$'), array('\\\\', '\\$'), (string)$value);
            }
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
 <command>
   <update>
     <domain:update
           xmlns:domain="http://www.rotld.ro/xml/epp/domain-1.0"
           xsi:schemaLocation="http://www.rotld.ro/xml/epp/domain-1.0 domain-1.0.xsd">
       <domain:name>{{ name }}</domain:name>
       {{ add }}
       {{ rem }}
     </domain:update>
   </update>
   <clTRID>{{ clTRID }}</clTRID>
 </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;

            $return = array(
                'code' => $code,
                'msg' => $msg
            );
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
    }

    /**
     * domainUpdateAuthinfo
     */
    public function domainUpdateAuthinfo($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        $return = array();
        try {
            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            $from[] = '/{{ authInfo }}/';
            $to[] = htmlspecialchars($params['authInfo']);
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-updateAuthinfo-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            foreach ($to as $key => $value) {
                $to[$key] = str_replace(array('\\', '$'), array('\\\\', '\\$'), (string)$value);
            }
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
 <command>
   <update>
     <domain:update
           xmlns:domain="http://www.rotld.ro/xml/epp/domain-1.0"
           xsi:schemaLocation="http://www.rotld.ro/xml/epp/domain-1.0 domain-1.0.xsd">
       <domain:name>{{ name }}</domain:name>
       <domain:chg>
         <domain:authInfo>
           <domain:pw>{{ authInfo }}</domain:pw>
         </domain:authInfo>
       </domain:chg>
     </domain:update>
   </update>
   <clTRID>{{ clTRID }}</clTRID>
 </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;

            $return = array(
                'code' => $code,
                'msg' => $msg
            );
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
    }

    /**
     * domainUpdateDNSSEC
     */
    public function domainUpdateDNSSEC($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        $return = array();
        try {
            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            if ($params['command'] == 'add') {
                $from[] = '/{{ add }}/';
                $to[] = "<secDNS:add>
                <secDNS:dsData>
            <secDNS:keyTag>".htmlspecialchars($params['keyTag_1'])."</secDNS:keyTag>
            <secDNS:alg>".htmlspecialchars($params['alg_1'])."</secDNS:alg>
            <secDNS:digestType>".htmlspecialchars($params['digestType_1'])."</secDNS:digestType>
            <secDNS:digest>".htmlspecialchars($params['digest_1'])."</secDNS:digest>
          </secDNS:dsData>
          </secDNS:add>";
                $from[] = '/{{ rem }}/';
                $to[] = "";
                $from[] = '/{{ addrem }}/';
                $to[] = "";
            } else if ($params['command'] == 'rem') {
                $from[] = '/{{ add }}/';
                $to[] = "";
                $from[] = '/{{ rem }}/';
                $to[] = "<secDNS:rem>
                <secDNS:dsData>
            <secDNS:keyTag>".htmlspecialchars($params['keyTag_1'])."</secDNS:keyTag>
            <secDNS:alg>".htmlspecialchars($params['alg_1'])."</secDNS:alg>
            <secDNS:digestType>".htmlspecialchars($params['digestType_1'])."</secDNS:digestType>
            <secDNS:digest>".htmlspecialchars($params['digest_1'])."</secDNS:digest>
          </secDNS:dsData>
          </secDNS:rem>";
                $from[] = '/{{ addrem }}/';
                $to[] = "";
            } else if ($params['command'] == 'addrem') {
                $from[] = '/{{ add }}/';
                $to[] = "";
                $from[] = '/{{ rem }}/';
                $to[] = "";
                $from[] = '/{{ addrem }}/';
                $to[] = "<secDNS:rem>
                <secDNS:dsData>
            <secDNS:keyTag>".htmlspecialchars($params['keyTag_1'])."</secDNS:keyTag>
            <secDNS:alg>".htmlspecialchars($params['alg_1'])."</secDNS:alg>
            <secDNS:digestType>".htmlspecialchars($params['digestType_1'])."</secDNS:digestType>
            <secDNS:digest>".htmlspecialchars($params['digest_1'])."</secDNS:digest>
          </secDNS:dsData>
          </secDNS:rem>
          <secDNS:add>
          <secDNS:dsData>
            <secDNS:keyTag>".htmlspecialchars($params['keyTag_2'])."</secDNS:keyTag>
            <secDNS:alg>".htmlspecialchars($params['alg_2'])."</secDNS:alg>
            <secDNS:digestType>".htmlspecialchars($params['digestType_2'])."</secDNS:digestType>
            <secDNS:digest>".htmlspecialchars($params['digest_2'])."</secDNS:digest>
          </secDNS:dsData>
          </secDNS:add>";
            }
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-updateDNSSEC-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            foreach ($to as $key => $value) {
                $to[$key] = str_replace(array('\\', '$'), array('\\\\', '\\$'), (string)$value);
            }
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
 <command>
   <update>
     <domain:update
           xmlns:domain="http://www.rotld.ro/xml/epp/domain-1.0"
           xsi:schemaLocation="http://www.rotld.ro/xml/epp/domain-1.0 domain-1.0.xsd">
       <domain:name>{{ name }}</domain:name>
     </domain:update>
   </update>
<extension>
      <secDNS:update
        xmlns:secDNS="urn:ietf:params:xml:ns:secDNS-1.1"
        xsi:schemaLocation="urn:ietf:params:xml:ns:secDNS-1.1 secDNS-1.1.xsd">
        {{ add }}
        {{ rem }}
        {{ addrem }}
      </secDNS:update>
    </extension>
   <clTRID>{{ clTRID }}</clTRID>
 </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;

            $return = array(
                'code' => $code,
                'msg' => $msg
            );
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
    }

    /**
     * domainTransfer
     */
    public function domainTransfer($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        $return = array();
        try {
            if (($params['op'] ?? '') !== 'request') {
                throw new EppException('RoTLD transfers are completed immediately; operation "' . ($params['op'] ?? '') . '" is not supported.');
            }
            if (empty($params['authInfoPw'])) {
                throw new EppException('RoTLD: authInfoPw must contain the registrar-transfer authorization key.');
            }

            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            $from[] = '/{{ authInfoPw }}/';
            $to[] = htmlspecialchars($params['authInfoPw']);
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-transfer-' . $clTRID);
            foreach ($to as $key => $value) {
                $to[$key] = str_replace(array('\\', '$'), array('\\\\', '\\$'), (string)$value);
            }
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <transfer op="request">
      <domain:transfer
       xmlns:domain="http://www.rotld.ro/xml/epp/domain-1.0"
       xsi:schemaLocation="http://www.rotld.ro/xml/epp/domain-1.0 domain-1.0.xsd">
        <domain:name>{{ name }}</domain:name>
      </domain:transfer>
    </transfer>
    <extension>
      <rotld:ext xmlns:rotld="http://www.rotld.ro/xml/epp/rotld-1.0">
        <rotld:transfer>
          <rotld:domain>
            <rotld:authorization_key>{{ authInfoPw }}</rotld:authorization_key>
          </rotld:domain>
        </rotld:transfer>
      </rotld:ext>
    </extension>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('http://www.rotld.ro/xml/epp/domain-1.0')->trnData;
            $name = (string)$r->name;
            $trStatus = (string)$r->trStatus;
            $reID = (string)$r->reID;
            $reDate = (string)$r->reDate;
            $acID = (string)$r->acID;
            $acDate = (string)$r->acDate;
            $exDate = (string)$r->exDate;

            $return = array(
                'code' => $code,
                'msg' => $msg,
                'name' => $name,
                'trStatus' => $trStatus,
                'reID' => $reID,
                'reDate' => $reDate,
                'acID' => $acID,
                'acDate' => $acDate,
                'exDate' => $exDate
            );
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
    }

    /**
     * domainRenew
     */
    public function domainRenew($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        $return = array();
        try {
            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-renew-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            foreach ($to as $key => $value) {
                $to[$key] = str_replace(array('\\', '$'), array('\\\\', '\\$'), (string)$value);
            }
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <info>
      <domain:info
       xmlns:domain="http://www.rotld.ro/xml/epp/domain-1.0"
       xsi:schemaLocation="http://www.rotld.ro/xml/epp/domain-1.0 domain-1.0.xsd">
        <domain:name hosts="all">{{ name }}</domain:name>
      </domain:info>
    </info>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $r = $r->response->resData->children('http://www.rotld.ro/xml/epp/domain-1.0')->infData;

            $expDate = (string)$r->exDate;
            $expDate = preg_replace("/^(\d+\-\d+\-\d+)\D.*$/", "$1", $expDate);
            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            $from[] = '/{{ regperiod }}/';
            $to[] = htmlspecialchars($params['regperiod']);
            $from[] = '/{{ expDate }}/';
            $to[] = htmlspecialchars($expDate);
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-renew-' . $clTRID);
            foreach ($to as $key => $value) {
                $to[$key] = str_replace(array('\\', '$'), array('\\\\', '\\$'), (string)$value);
            }
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <renew>
      <domain:renew
       xmlns:domain="http://www.rotld.ro/xml/epp/domain-1.0">
        <domain:name>{{ name }}</domain:name>
        <domain:curExpDate>{{ expDate }}</domain:curExpDate>
        <domain:period unit="y">{{ regperiod }}</domain:period>
      </domain:renew>
    </renew>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('http://www.rotld.ro/xml/epp/domain-1.0')->renData;
            $name = (string)$r->name;
            $exDate = (string)$r->exDate;

            $return = array(
                'code' => $code,
                'msg' => $msg,
                'name' => $name,
                'exDate' => $exDate
            );
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
    }

    /**
     * domainDelete
     */
    public function domainDelete($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        $return = array();
        try {
            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-delete-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            foreach ($to as $key => $value) {
                $to[$key] = str_replace(array('\\', '$'), array('\\\\', '\\$'), (string)$value);
            }
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <delete>
      <domain:delete
       xmlns:domain="http://www.rotld.ro/xml/epp/domain-1.0">
        <domain:name>{{ name }}</domain:name>
      </domain:delete>
    </delete>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;

            $return = array(
                'code' => $code,
                'msg' => $msg
            );
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
    }

    /**
     * domainRestore
     */
    public function domainRestore($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        throw new EppException('RGP extension is not implemented in this profile!');
    }

    /**
     * domainReport
     */
    public function domainReport($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        throw new EppException('RGP extension is not implemented in this profile!');
    }
}