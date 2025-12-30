<?php
/**
 * Tembo EPP client library
 *
 * Written in 2023-2025 by Taras Kondratyuk (https://namingo.org)
 * Based on xpanel/epp-bundle written in 2019 by Lilian Rudenco (info@xpanel.com)
 *
 * @license MIT
 */

namespace Pinga\Tembo\Registries;

use Pinga\Tembo\Epp;
use Pinga\Tembo\EppRegistryInterface;
use Pinga\Tembo\Exception\EppException;
use Pinga\Tembo\Exception\EppNotConnectedException;

class VrsnEpp extends Epp
{
    protected function addLoginObjects(\XMLWriter $xml): void
    {
        $xml->writeElement('objURI', 'urn:ietf:params:xml:ns:domain-1.0');
        $xml->writeElement('objURI', 'urn:ietf:params:xml:ns:contact-1.0');
        $xml->writeElement('objURI', 'urn:ietf:params:xml:ns:host-1.0');
        $xml->writeElement('objURI', 'http://www.verisign.com/epp/registry-1.0');
        $xml->writeElement('objURI', 'http://www.verisign.com/epp/lowbalance-poll-1.0');
        $xml->writeElement('objURI', 'http://www.verisign.com/epp/rgp-poll-1.0');
    }

    protected function addLoginExtensions(\XMLWriter $xml): void
    {
        $xml->startElement('svcExtension');
        $xml->writeElement('extURI', 'urn:ietf:params:xml:ns:secDNS-1.1');
        $xml->writeElement('extURI', 'urn:ietf:params:xml:ns:epp:loginSec-1.0');
        $xml->writeElement('extURI', 'http://www.verisign.com/epp/whoisInf-1.0');
        $xml->writeElement('extURI', 'http://www.verisign.com/epp/idnLang-1.0');
        $xml->writeElement('extURI', 'urn:ietf:params:xml:ns:coa-1.0');
        $xml->writeElement('extURI', 'http://www.verisign-grs.com/epp/namestoreExt-1.1');
        $xml->writeElement('extURI', 'http://www.verisign.com/epp/sync-1.0');
        $xml->writeElement('extURI', 'http://www.verisign.com/epp/relatedDomain-1.0');
        $xml->writeElement('extURI', 'urn:ietf:params:xml:ns:verificationCode-1.0');
        $xml->writeElement('extURI', 'urn:ietf:params:xml:ns:rgp-1.0');
        $xml->writeElement('extURI', 'urn:ietf:params:xml:ns:changePoll-1.0');
        $xml->endElement(); // svcExtension
    }

    /**
     * hostCheck
     */
    public function hostCheck($params = array())
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
            $to[] = htmlspecialchars($params['hostname']);
            $from[] = '/{{ clTRID }}/';
            $microtime = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-host-check-' . $microtime);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <check>
      <host:check
        xmlns:host="urn:ietf:params:xml:ns:host-1.0"
        xsi:schemaLocation="urn:ietf:params:xml:ns:host-1.0 host-1.0.xsd">
        <host:name>{{ name }}</host:name>
      </host:check>
    </check>
  <extension>
    <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
      <namestoreExt:subProduct>dotCOM</namestoreExt:subProduct>
    </namestoreExt:namestoreExt>
  </extension>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('urn:ietf:params:xml:ns:host-1.0')->chkData;

            $i = 0;
            foreach ($r->cd as $cd) {
                $i++;
                $hosts[$i]['name'] = (string)$cd->name;
                $hosts[$i]['reason'] = (string)$cd->reason;
                $hosts[$i]['avail'] = (int)$cd->name->attributes()->avail;
            }

            $return = array(
                'code' => $code,
                'msg' => $msg,
                'hosts' => $hosts
            );
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
    }

    /**
     * hostInfo
     */
    public function hostInfo($params = array())
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
            $to[] = htmlspecialchars($params['hostname']);
            $from[] = '/{{ clTRID }}/';
            $microtime = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-host-info-' . $microtime);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
   <info>
     <host:info
      xmlns:host="urn:ietf:params:xml:ns:host-1.0">
       <host:name>{{ name }}</host:name>
     </host:info>
   </info>
  <extension>
    <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
      <namestoreExt:subProduct>dotCOM</namestoreExt:subProduct>
    </namestoreExt:namestoreExt>
  </extension>
   <clTRID>{{ clTRID }}</clTRID>
 </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('urn:ietf:params:xml:ns:host-1.0')->infData[0];
            $name = (string)$r->name;
            $addr = array();
            foreach ($r->addr as $ns) {
                $addr[] = (string)$ns;
            }
            $status = array();
            $i = 0;
            foreach ($r->status as $e) {
                $i++;
                $status[$i] = (string)$e->attributes()->s;
            }
            $clID = (string)$r->clID;
            $crID = (string)$r->crID;
            $crDate = (string)$r->crDate;
            $upID = (string)$r->upID;
            $upDate = (string)$r->upDate;

            $return = array(
                'code' => $code,
                'msg' => $msg,
                'name' => $name,
                'status' => $status,
                'addr' => $addr,
                'clID' => $clID,
                'crID' => $crID,
                'crDate' => $crDate,
                'upID' => $upID,
                'upDate' => $upDate
            );
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
    }

    /**
     * hostCreate
     */
    public function hostCreate($params = array())
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
            $to[] = htmlspecialchars($params['hostname']);
            $from[] = '/{{ ip }}/';
            $to[] = htmlspecialchars($params['ipaddress'] ?? '');
            $from[] = '/{{ v }}/';
            $to[] = (preg_match('/:/', $params['ipaddress'] ?? '') ? 'v6' : 'v4');
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-host-create-' . $clTRID);
            $from[] = "/<\w+:\w+(?:\s+[^>]*)?>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <create>
      <host:create
       xmlns:host="urn:ietf:params:xml:ns:host-1.0">
        <host:name>{{ name }}</host:name>
        <host:addr ip="{{ v }}">{{ ip }}</host:addr>
      </host:create>
    </create>
  <extension>
    <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
      <namestoreExt:subProduct>dotCOM</namestoreExt:subProduct>
    </namestoreExt:namestoreExt>
  </extension>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('urn:ietf:params:xml:ns:host-1.0')->creData;
            $name = (string)$r->name;

            $return = array(
                'code' => $code,
                'msg' => $msg,
                'name' => $name
            );
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
    }
    
    /**
     * hostUpdate
     */
    public function hostUpdate($params = array())
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
            $to[] = htmlspecialchars($params['hostname']);
            $from[] = '/{{ ip1 }}/';
            $to[] = htmlspecialchars($params['currentipaddress']);
            $from[] = '/{{ v1 }}/';
            $to[] = (preg_match('/:/', $params['currentipaddress']) ? 'v6' : 'v4');
            $from[] = '/{{ ip2 }}/';
            $to[] = htmlspecialchars($params['newipaddress']);
            $from[] = '/{{ v2 }}/';
            $to[] = (preg_match('/:/', $params['newipaddress']) ? 'v6' : 'v4');
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-host-update-' . $clTRID);
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <update>
      <host:update
       xmlns:host="urn:ietf:params:xml:ns:host-1.0">
        <host:name>{{ name }}</host:name>
        <host:add>
          <host:addr ip="{{ v2 }}">{{ ip2 }}</host:addr>
        </host:add>
        <host:rem>
          <host:addr ip="{{ v1 }}">{{ ip1 }}</host:addr>
        </host:rem>
      </host:update>
    </update>
  <extension>
    <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
      <namestoreExt:subProduct>dotCOM</namestoreExt:subProduct>
    </namestoreExt:namestoreExt>
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
     * hostDelete
     */
    public function hostDelete($params = array())
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
            $to[] = htmlspecialchars($params['hostname']);
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-host-delete-' . $clTRID);
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
    <epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
      <command>
        <delete>
          <host:delete
           xmlns:host="urn:ietf:params:xml:ns:host-1.0">
            <host:name>{{ name }}</host:name>
          </host:delete>
        </delete>
  <extension>
    <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
      <namestoreExt:subProduct>dotCOM</namestoreExt:subProduct>
    </namestoreExt:namestoreExt>
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
     * contactCheck
     */
    public function contactCheck($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }
        
        throw new EppException("Contacts not supported!");
    }

    /**
     * contactInfo
     */
    public function contactInfo($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }
        
        throw new EppException("Contacts not supported!");
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
        
        throw new EppException("Contacts not supported!");
    }

    /**
     * contactUpdate
     */
    public function contactUpdate($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }
        
        throw new EppException("Contacts not supported!");
    }

    /**
     * contactDelete
     */
    public function contactDelete($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }
        
        throw new EppException("Contacts not supported!");
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
            $tld_text = '';
            foreach ($params['domains'] as $name) {
                $text .= '<domain:name>' . $name . '</domain:name>' . "\n";
                $parts = explode('.', $name);
                $tld = end($parts);
                $tld_text .= '<namestoreExt:subProduct>' . $tld . '</namestoreExt:subProduct>' . "\n";
            }
            $from[] = '/{{ names }}/';
            $to[] = $text;
            $from[] = '/{{ tld }}/';
            $to[] = $tld_text;
            $from[] = '/{{ clTRID }}/';
            $microtime = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-check-' . $microtime);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <check>
      <domain:check
        xmlns:domain="urn:ietf:params:xml:ns:domain-1.0"
        xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
        {{ names }}
      </domain:check>
    </check>
    <extension>
      <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
         {{ tld }}
      </namestoreExt:namestoreExt>
    </extension>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('urn:ietf:params:xml:ns:domain-1.0')->chkData;

            $i = 0;
            foreach ($r->cd as $cd) {
                $i++;
                $domains[$i]['name'] = (string)$cd->name;
                $availStr = (string)$cd->name->attributes()->avail;
                $domains[$i]['avail'] = ($availStr === 'true' || $availStr === '1') ? true : false;
                $domains[$i]['reason'] = (string)$cd->reason;
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

        throw new EppException("Launch extension not supported!");
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
            $tld = strtoupper(str_replace('.', '', strstr($params['domainname'], '.')));
            $from[] = '/{{ tld }}/';
            $to[] = $tld;
            $from[] = '/{{ clTRID }}/';
            $microtime = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-info-' . $microtime);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <info>
      <domain:info
       xmlns:domain="urn:ietf:params:xml:ns:domain-1.0"
       xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
        <domain:name hosts="all">{{ domainname }}</domain:name>
        {{ authInfo }}
      </domain:info>
    </info>
    <extension>
      <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
        <namestoreExt:subProduct>dot{{ tld }}</namestoreExt:subProduct>
      </namestoreExt:namestoreExt>
    </extension>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('urn:ietf:params:xml:ns:domain-1.0')->infData;
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
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
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
            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            $tld = strtoupper(str_replace('.', '', strstr($params['domainname'], '.')));
            $from[] = '/{{ tld }}/';
            $to[] = $tld;
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-info-' . $clTRID);
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
    <epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
      <command>
        <info>
          <domain:info
           xmlns:domain="urn:ietf:params:xml:ns:domain-1.0"
           xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
            <domain:name hosts="all">{{ name }}</domain:name>
          </domain:info>
        </info>
        <extension>
          <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
            <namestoreExt:subProduct>dot{{ tld }}</namestoreExt:subProduct>
          </namestoreExt:namestoreExt>
        </extension>
        <clTRID>{{ clTRID }}</clTRID>
      </command>
    </epp>');
            $r = $this->writeRequest($xml);
            $r = $r->response->resData->children('urn:ietf:params:xml:ns:domain-1.0')->infData;

            $add = $rem = array();
            $i = 0;
            foreach ($r->ns->hostObj as $ns) {
                $i++;
                $ns = (string)$ns;
                if (!$ns) {
                    continue;
                }

                $rem["ns{$i}"] = $ns;
            }

            foreach ($params as $k => $v) {
                if (!$v) {
                    continue;
                }

                if (!preg_match("/^ns\d$/i", $k)) {
                    continue;
                }

                if ($k0 = array_search($v, $rem)) {
                    unset($rem[$k0]);
                } else {
                    $add[$k] = $v;
                }
            }

            if (!empty($add) || !empty($rem)) {
                $from = $to = array();
                $text = '';
                foreach ($add as $k => $v) {
                    $text.= '<domain:hostObj>' . $v . '</domain:hostObj>' . "\n";
                }

                $from[] = '/{{ add }}/';
                $to[] = (empty($text) ? '' : "<domain:add><domain:ns>\n{$text}</domain:ns></domain:add>\n");
                $text = '';
                foreach ($rem as $k => $v) {
                    $text.= '<domain:hostObj>' . $v . '</domain:hostObj>' . "\n";
                }

                $from[] = '/{{ rem }}/';
                $to[] = (empty($text) ? '' : "<domain:rem><domain:ns>\n{$text}</domain:ns></domain:rem>\n");
                $from[] = '/{{ name }}/';
                $to[] = htmlspecialchars($params['domainname']);
                $tld = strtoupper(str_replace('.', '', strstr($params['domainname'], '.')));
                $from[] = '/{{ tld }}/';
                $to[] = $tld;
                $from[] = '/{{ clTRID }}/';
                $clTRID = str_replace('.', '', round(microtime(1), 3));
                $to[] = htmlspecialchars($this->prefix . '-domain-updateNS-' . $clTRID);
                $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
    <epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
      <command>
        <update>
          <domain:update
           xmlns:domain="urn:ietf:params:xml:ns:domain-1.0"
           xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
            <domain:name>{{ name }}</domain:name>
        {{ add }}
        {{ rem }}
          </domain:update>
        </update>
        <extension>
          <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
            <namestoreExt:subProduct>dot{{ tld }}</namestoreExt:subProduct>
          </namestoreExt:namestoreExt>
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
            }
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
        
        throw new EppException("Contacts not supported!");
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
            $tld = strtoupper(str_replace('.', '', strstr($params['domainname'], '.')));
            $from[] = '/{{ tld }}/';
            $to[] = $tld;
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-updateStatus-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
 <command>
   <update>
     <domain:update
           xmlns:domain="urn:ietf:params:xml:ns:domain-1.0"
           xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
       <domain:name>{{ name }}</domain:name>
       {{ add }}
       {{ rem }}
     </domain:update>
   </update>
   <extension>
     <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
       <namestoreExt:subProduct>dot{{ tld }}</namestoreExt:subProduct>
     </namestoreExt:namestoreExt>
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
            $tld = strtoupper(str_replace('.', '', strstr($params['domainname'], '.')));
            $from[] = '/{{ tld }}/';
            $to[] = $tld;
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-updateAuthinfo-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
 <command>
   <update>
     <domain:update
           xmlns:domain="urn:ietf:params:xml:ns:domain-1.0"
           xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
       <domain:name>{{ name }}</domain:name>
       <domain:chg>
         <domain:authInfo>
           <domain:pw>{{ authInfo }}</domain:pw>
         </domain:authInfo>
       </domain:chg>
     </domain:update>
   </update>
   <extension>
     <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
       <namestoreExt:subProduct>dot{{ tld }}</namestoreExt:subProduct>
     </namestoreExt:namestoreExt>
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
            $tld = strtoupper(str_replace('.', '', strstr($params['domainname'], '.')));
            $from[] = '/{{ tld }}/';
            $to[] = $tld;
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-updateDNSSEC-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
 <command>
   <update>
     <domain:update
           xmlns:domain="urn:ietf:params:xml:ns:domain-1.0"
           xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
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
     <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
       <namestoreExt:subProduct>dot{{ tld }}</namestoreExt:subProduct>
     </namestoreExt:namestoreExt>
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
            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            switch (htmlspecialchars($params['op'])) {
                case 'request':
                    $from[] = '/{{ years }}/';
                    $to[] = (int)($params['years']);
                    $from[] = '/{{ authInfoPw }}/';
                    $to[] = htmlspecialchars($params['authInfoPw']);
                    $xmltype = 'req';
                    break;
                case 'query':
                    $from[] = '/{{ type }}/';
                    $to[] = 'query';
                    $xmltype = 'oth';
                    break;
                case 'cancel':
                    $from[] = '/{{ type }}/';
                    $to[] = 'cancel';
                    $xmltype = 'oth';
                    break;
                case 'reject':
                    $from[] = '/{{ type }}/';
                    $to[] = 'reject';
                    $xmltype = 'oth';
                    break;
                case 'approve':
                    $xmltype = 'apr';
                    break;
                default:
                    throw new EppException('Invalid value for transfer:op specified.');
                    break;
            }
            $tld = strtoupper(str_replace('.', '', strstr($params['domainname'], '.')));
            $from[] = '/{{ tld }}/';
            $to[] = $tld;
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-transfer-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            if ($xmltype === 'req') {
                $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
            <epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
              <command>
                <transfer op="request">
                  <domain:transfer
                   xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
                    <domain:name>{{ name }}</domain:name>
                    <domain:period unit="y">{{ years }}</domain:period>
                    <domain:authInfo>
                      <domain:pw>{{ authInfoPw }}</domain:pw>
                    </domain:authInfo>
                  </domain:transfer>
                </transfer>
                <extension>
                  <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
                    <namestoreExt:subProduct>dot{{ tld }}</namestoreExt:subProduct>
                  </namestoreExt:namestoreExt>
                </extension>
                <clTRID>{{ clTRID }}</clTRID>
              </command>
            </epp>');
            } else if ($xmltype === 'apr') {
                $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
            <epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
              <command>
                <transfer op="approve">
                  <domain:transfer
                   xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
                    <domain:name>{{ name }}</domain:name>
                  </domain:transfer>
                </transfer>
                <extension>
                  <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
                    <namestoreExt:subProduct>dot{{ tld }}</namestoreExt:subProduct>
                  </namestoreExt:namestoreExt>
                </extension>
                <clTRID>{{ clTRID }}</clTRID>
              </command>
            </epp>');
            } else if ($xmltype === 'oth') {
                $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
            <epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
              <command>
                <transfer op="{{ type }}">
                  <domain:transfer
                   xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
                    <domain:name>{{ name }}</domain:name>
                  </domain:transfer>
                </transfer>
                <extension>
                  <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
                    <namestoreExt:subProduct>dot{{ tld }}</namestoreExt:subProduct>
                  </namestoreExt:namestoreExt>
                </extension>
                <clTRID>{{ clTRID }}</clTRID>
              </command>
            </epp>');
            }
            
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('urn:ietf:params:xml:ns:domain-1.0')->trnData;
            $name = (string)($r->name ?? 'N/A');
            $trStatus = (string)($r->trStatus ?? 'N/A');
            $reID = (string)($r->reID ?? 'N/A');
            $reDate = (string)($r->reDate ?? 'N/A');
            $acID = (string)($r->acID ?? 'N/A');
            $acDate = (string)($r->acDate ?? 'N/A');
            $exDate = (string)($r->exDate ?? 'N/A');

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
            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            $from[] = '/{{ period }}/';
            $to[] = (int)($params['period']);
            if (isset($params['nss'])) {
                $text = '';
                foreach ($params['nss'] as $hostObj) {
                    $text .= '<domain:hostObj>' . $hostObj . '</domain:hostObj>' . "\n";
                }
                $from[] = '/{{ hostObjs }}/';
                $to[] = $text;
            } else {
                $from[] = '/{{ hostObjs }}/';
                $to[] = '';
            }
            $tld = strtoupper(str_replace('.', '', strstr($params['domainname'], '.')));
            $from[] = '/{{ tld }}/';
            $to[] = $tld;
            $from[] = '/{{ authInfoPw }}/';
            $to[] = htmlspecialchars($params['authInfoPw']);
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-create-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <create>
      <domain:create
       xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
        <domain:name>{{ name }}</domain:name>
        <domain:period unit="y">{{ period }}</domain:period>
        <domain:ns>
          {{ hostObjs }}
        </domain:ns>
        <domain:authInfo>
          <domain:pw>{{ authInfoPw }}</domain:pw>
        </domain:authInfo>
      </domain:create>
    </create>
    <extension>
      <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
        <namestoreExt:subProduct>dot{{ tld }}</namestoreExt:subProduct>
      </namestoreExt:namestoreExt>
    </extension>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('urn:ietf:params:xml:ns:domain-1.0')->creData;
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
            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            $from[] = '/{{ period }}/';
            $to[] = (int)($params['period']);
            if (isset($params['nss'])) {
                $text = '';
                foreach ($params['nss'] as $hostObj) {
                    $text .= '<domain:hostObj>' . $hostObj . '</domain:hostObj>' . "\n";
                }
                $from[] = '/{{ hostObjs }}/';
                $to[] = $text;
            } else {
                $from[] = '/{{ hostObjs }}/';
                $to[] = '';
            }
            $from[] = '/{{ authInfoPw }}/';
            $to[] = htmlspecialchars($params['authInfoPw']);
            if ($params['dnssec_records'] == 1) {
                $from[] = '/{{ dnssec_data }}/';
                $to[] = "<secDNS:dsData>
            <secDNS:keyTag>".htmlspecialchars($params['keyTag_1'])."</secDNS:keyTag>
            <secDNS:alg>".htmlspecialchars($params['alg_1'])."</secDNS:alg>
            <secDNS:digestType>".htmlspecialchars($params['digestType_1'])."</secDNS:digestType>
            <secDNS:digest>".htmlspecialchars($params['digest_1'])."</secDNS:digest>
          </secDNS:dsData>";
            } else if ($params['dnssec_records'] == 2) {
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
            $tld = strtoupper(str_replace('.', '', strstr($params['domainname'], '.')));
            $from[] = '/{{ tld }}/';
            $to[] = $tld;
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-createDNSSEC-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <create>
      <domain:create
       xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
        <domain:name>{{ name }}</domain:name>
        <domain:period unit="y">{{ period }}</domain:period>
        <domain:ns>
          {{ hostObjs }}
        </domain:ns>
        <domain:authInfo>
          <domain:pw>{{ authInfoPw }}</domain:pw>
        </domain:authInfo>
      </domain:create>
    </create>
    <extension>
      <secDNS:create xmlns:secDNS="urn:ietf:params:xml:ns:secDNS-1.1">
        {{ dnssec_data }}
      </secDNS:create>
      <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
        <namestoreExt:subProduct>dot{{ tld }}</namestoreExt:subProduct>
      </namestoreExt:namestoreExt>
    </extension>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('urn:ietf:params:xml:ns:domain-1.0')->creData;
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

        throw new EppException("Launch extension not supported!");
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

        throw new EppException("Launch extension not supported!");
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
            $tld = strtoupper(str_replace('.', '', strstr($params['domainname'], '.')));
            $from[] = '/{{ tld }}/';
            $to[] = $tld;
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-renew-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <info>
      <domain:info
       xmlns:domain="urn:ietf:params:xml:ns:domain-1.0"
       xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
        <domain:name hosts="all">{{ name }}</domain:name>
      </domain:info>
    </info>
    <extension>
      <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
        <namestoreExt:subProduct>dot{{ tld }}</namestoreExt:subProduct>
      </namestoreExt:namestoreExt>
    </extension>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $r = $r->response->resData->children('urn:ietf:params:xml:ns:domain-1.0')->infData;

            $expDate = (string)$r->exDate;
            $expDate = preg_replace("/^(\d+\-\d+\-\d+)\D.*$/", "$1", $expDate);
            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            $from[] = '/{{ regperiod }}/';
            $to[] = htmlspecialchars($params['regperiod']);
            $from[] = '/{{ expDate }}/';
            $to[] = htmlspecialchars($expDate);
            $tld = strtoupper(str_replace('.', '', strstr($params['domainname'], '.')));
            $from[] = '/{{ tld }}/';
            $to[] = $tld;
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-renew-' . $clTRID);
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <renew>
      <domain:renew
       xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
        <domain:name>{{ name }}</domain:name>
        <domain:curExpDate>{{ expDate }}</domain:curExpDate>
        <domain:period unit="y">{{ regperiod }}</domain:period>
      </domain:renew>
    </renew>
    <extension>
      <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
        <namestoreExt:subProduct>dot{{ tld }}</namestoreExt:subProduct>
      </namestoreExt:namestoreExt>
    </extension>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('urn:ietf:params:xml:ns:domain-1.0')->renData;
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
            $tld = strtoupper(str_replace('.', '', strstr($params['domainname'], '.')));
            $from[] = '/{{ tld }}/';
            $to[] = $tld;
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-delete-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <delete>
      <domain:delete
       xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
        <domain:name>{{ name }}</domain:name>
      </domain:delete>
    </delete>
    <extension>
      <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
        <namestoreExt:subProduct>dot{{ tld }}</namestoreExt:subProduct>
      </namestoreExt:namestoreExt>
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

        $return = array();
        try {
            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            $tld = strtoupper(str_replace('.', '', strstr($params['domainname'], '.')));
            $from[] = '/{{ tld }}/';
            $to[] = $tld;
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-restore-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
  <command>
    <update>
      <domain:update xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
        <domain:name>{{ name }}</domain:name>
        <domain:chg/>
      </domain:update>
    </update>
    <extension>
      <rgp:update xmlns:rgp="urn:ietf:params:xml:ns:rgp-1.0">
        <rgp:restore op="request"/>
      </rgp:update>
      <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
        <namestoreExt:subProduct>dot{{ tld }}</namestoreExt:subProduct>
      </namestoreExt:namestoreExt>
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

        $return = array();
        try {
            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            $tld = strtoupper(str_replace('.', '', strstr($params['domainname'], '.')));
            $from[] = '/{{ tld }}/';
            $to[] = $tld;
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-report-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
   <epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0
        epp-1.0.xsd">
     <command>
       <update>
         <domain:update
          xmlns:domain="urn:ietf:params:xml:ns:domain-1.0"
          xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0
          domain-1.0.xsd">
           <domain:name>{{ name }}</domain:name>
           <domain:chg/>
         </domain:update>
       </update>
       <extension>
         <rgp:update xmlns:rgp="urn:ietf:params:xml:ns:rgp-1.0"
          xsi:schemaLocation="urn:ietf:params:xml:ns:rgp-1.0
          rgp-1.0.xsd">
           <rgp:restore op="report">
             <rgp:report>
               <rgp:preData>Pre-delete registration data goes here.
               Both XML and free text are allowed.</rgp:preData>
               <rgp:postData>Post-restore registration data goes here.
               Both XML and free text are allowed.</rgp:postData>
               <rgp:delTime>2019-10-10T22:00:00.0Z</rgp:delTime>
               <rgp:resTime>2019-10-20T22:00:00.0Z</rgp:resTime>
               <rgp:resReason>Registrant error.</rgp:resReason>
               <rgp:statement>This registrar has not restored the
               Registered Name in order to assume the rights to use
               or sell the Registered Name for itself or for any
               third party.</rgp:statement>
               <rgp:statement>The information in this report is
               true to best of this registrars knowledge, and this
               registrar acknowledges that intentionally supplying
               false information in this report shall constitute an
               incurable material breach of the
               Registry-Registrar Agreement.</rgp:statement>
               <rgp:other>Supporting information goes
               here.</rgp:other>
             </rgp:report>
           </rgp:restore>
         </rgp:update>
         <namestoreExt:namestoreExt xmlns:namestoreExt="http://www.verisign-grs.com/epp/namestoreExt-1.1">
           <namestoreExt:subProduct>dot{{ tld }}</namestoreExt:subProduct>
         </namestoreExt:namestoreExt>
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

}