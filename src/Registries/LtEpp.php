<?php
/**
 * Namingo EPP client library
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

class LtEpp extends Epp
{
    protected function addLoginObjects(\XMLWriter $xml): void
    {
        $xml->writeElement('objURI', 'urn:ietf:params:xml:ns:epp-1.0');
        $xml->writeElement('objURI', 'http://www.domreg.lt/epp/xml/domreg-domain-1.2');
        $xml->writeElement('objURI', 'http://www.domreg.lt/epp/xml/domreg-contact-1.1');
        $xml->writeElement('objURI', 'http://www.domreg.lt/epp/xml/domreg-nsgroup-1.0');
        $xml->writeElement('objURI', 'http://www.domreg.lt/epp/xml/domreg-event-1.0');
        $xml->writeElement('objURI', 'http://www.domreg.lt/epp/xml/domreg-permit-1.1');
        $xml->writeElement('objURI', 'http://www.domreg.lt/epp/xml/domreg-rlock-1.0');
        $xml->writeElement('objURI', 'urn:ietf:params:xml:ns:secDNS-1.1');
        $xml->writeElement('objURI', 'http://www.domreg.lt/epp/xml/authInfo-1.0');
    }

    protected function addLoginExtensions(\XMLWriter $xml): void
    {
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
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <check>
      <nsgroup:check xmlns:nsgroup="http://www.domreg.lt/epp/xml/domreg-nsgroup-1.0">
      <nsgroup:name>{{ name }}</nsgroup:name>
      </nsgroup:check>
    </check>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('http://www.domreg.lt/epp/xml/domreg-nsgroup-1.0')->chkData;

            $i = 0;
            foreach ($r->cd as $cd) {
                $i++;
                $hosts[$i]['name'] = (string)$cd->name;
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
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
   <info>
     <nsgroup:info xmlns:nsgroup="http://www.domreg.lt/epp/xml/domreg-nsgroup-1.0">
       <nsgroup:name>{{ name }}</nsgroup:name>
     </nsgroup:info>
   </info>
   <clTRID>{{ clTRID }}</clTRID>
 </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('http://www.domreg.lt/epp/xml/domreg-nsgroup-1.0')->infData[0];
            $name = (string)$r->name;
            $addr = array();
            foreach ($r->addr as $ns) {
                $addr[] = (string)$ns;
            }

            $return = array(
                'code' => $code,
                'msg' => $msg,
                'name' => $name,
                'addr' => $addr
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
            $nsXml = '';
            if (!empty($params['ns']) && is_array($params['ns'])) {
                foreach (array_slice($params['ns'], 0, 13) as $ns) {
                    $nsXml .= '<nsgroup:ns>' . htmlspecialchars($ns) . '</nsgroup:ns>';
                }
            }
            $from[] = '/{{ ns }}/';
            $to[]   = $nsXml;
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-host-create-' . $clTRID);
            $from[] = "/<\w+:\w+(?:\s+[^>]*)?>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <create>
      <nsgroup:create xmlns:nsgroup="http://www.domreg.lt/epp/xml/domreg-nsgroup-1.0">
        <nsgroup:name>{{ name }}</nsgroup:name>
        {{ ns }}
      </nsgroup:create>
    </create>
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
            $nsXml = '';
            if (!empty($params['ns']) && is_array($params['ns'])) {
                foreach (array_slice($params['ns'], 0, 13) as $ns) {
                    $nsXml .= '<nsgroup:ns>' . htmlspecialchars($ns) . '</nsgroup:ns>';
                }
            }
            $from[] = '/{{ ns }}/';
            $to[]   = $nsXml;
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-host-update-' . $clTRID);
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <update>
      <nsgroup:update xmlns:nsgroup="http://www.domreg.lt/epp/xml/domreg-nsgroup-1.0">
        <nsgroup:name>{{ name }}</nsgroup:name>
        {{ ns }}
      </nsgroup:update>
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
     * hostUpdateStatus
     */
    public function hostUpdateStatus($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        throw new EppException("Host update status is not supported!");
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
    <epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
      <command>
        <delete>  
          <nsgroup:delete xmlns:nsgroup="http://www.domreg.lt/epp/xml/domreg-nsgroup-1.0">
            <nsgroup:name>{{ name }}</nsgroup:name>
          </nsgroup:delete>
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
        
        throw new EppException("Contact check is not supported!");
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

        $return = array();
        try {
            $from = $to = array();
            $from[] = '/{{ id }}/';
            $to[] = htmlspecialchars($params['contact']);
            $from[] = '/{{ authInfo }}/';
            $authInfo = (isset($params['authInfoPw']) ? "<contact:authInfo>\n<contact:pw><![CDATA[{$params['authInfoPw']}]]></contact:pw>\n</contact:authInfo>" : '');
            $to[] = $authInfo;
            $from[] = '/{{ clTRID }}/';
            $microtime = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-contact-info-' . $microtime);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <info>
      <contact:info
       xmlns:contact="http://www.domreg.lt/epp/xml/domreg-contact-1.1">
        <contact:id>{{ id }}</contact:id>
        {{ authInfo }}
      </contact:info>
    </info>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('http://www.domreg.lt/epp/xml/domreg-contact-1.1')->infData[0];

            foreach ($r->postalInfo as $e) {
                $name = (string)$e->name;
                $org = (string)$e->org;
                $street1 = $street2 = $street3 = '';
                for ($i = 0; $i <= 2; $i++) {
                    ${'street' . ($i + 1)} = (string)$e->addr->street[$i];
                }
                $city = (string)$e->addr->city;
                $state = (string)$e->addr->sp;
                $postal = (string)$e->addr->pc;
                $country = (string)$e->addr->cc;
            }
            $id = (string)$r->id;
            $status = array();
            $i = 0;
            foreach ($r->status as $e) {
                $i++;
                $status[$i] = (string)$e->attributes()->s;
            }
            $voice = (string)$r->voice;
            $email = (string)$r->email;
            $role = (string)$r->role;
            $crDate = (string)$r->crDate;
            $upDate = (string)$r->upDate;

            $return = array(
                'id' => $id,
                'code' => $code,
                'status' => $status,
                'msg' => $msg,
                'name' => $name,
                'org' => $org,
                'street1' => $street1,
                'street2' => $street2,
                'street3' => $street3,
                'city' => $city,
                'state' => $state,
                'postal' => $postal,
                'country' => $country,
                'voice' => $voice,
                'email' => $email,
                'role' => $role,
                'crDate' => $crDate,
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
            $from = $to = array();
            $from[] = '/{{ type }}/';
            $to[] = htmlspecialchars($params['type']);
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['firstname'] . ' ' . $params['lastname']);
            $from[] = '/{{ org }}/';
            $to[] = htmlspecialchars($params['companyname']);
            $from[] = '/{{ street1 }}/';
            $to[] = htmlspecialchars($params['address1']);
            $from[] = '/{{ city }}/';
            $to[] = htmlspecialchars($params['city']);
            $from[] = '/{{ state }}/';
            $to[] = htmlspecialchars($params['state']);
            $from[] = '/{{ postcode }}/';
            $to[] = htmlspecialchars($params['postcode']);
            $from[] = '/{{ country }}/';
            $to[] = htmlspecialchars($params['country']);
            $from[] = '/{{ phonenumber }}/';
            $to[] = htmlspecialchars($params['fullphonenumber']);
            $from[] = '/{{ email }}/';
            $to[] = htmlspecialchars($params['email']);
            $from[] = '/{{ orgcode }}/';
            $to[] = htmlspecialchars($params['orgcode']);
            $from[] = '/{{ role }}/';
            $to[] = htmlspecialchars($params['role']);
            $from[] = '/{{ clTRID }}/';
            $microtime = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-contact-create-' . $microtime);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <create>
      <contact:create
       xmlns:contact="http://www.domreg.lt/epp/xml/domreg-contact-1.1">
        <contact:postalInfo type="{{ type }}">
          <contact:name>{{ name }}</contact:name>
          <contact:org>{{ org }}</contact:org>
          <contact:addr>
            <contact:street>{{ street1 }}</contact:street>
            <contact:city>{{ city }}</contact:city>
            <contact:sp>{{ state }}</contact:sp>
            <contact:pc>{{ postcode }}</contact:pc>
            <contact:cc>{{ country }}</contact:cc>
          </contact:addr>
        </contact:postalInfo>
        <contact:voice>{{ phonenumber }}</contact:voice>
        <contact:email>{{ email }}</contact:email>
        <contact:orgcode>{{ orgcode }}</contact:orgcode>
        <contact:role>{{ role }}</contact:role>
      </contact:create>
    </create>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('http://www.domreg.lt/epp/xml/domreg-contact-1.1')->creData;
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

        $return = array();
        try {
            $from = $to = array();
            $from[] = '/{{ type }}/';
            $to[] = htmlspecialchars($params['type']);
            $from[] = '/{{ id }}/';
            $to[] = htmlspecialchars($params['id']);
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['firstname'] . ' ' . $params['lastname']);
            $from[] = '/{{ org }}/';
            $to[] = htmlspecialchars($params['companyname']);
            $from[] = '/{{ street1 }}/';
            $to[] = htmlspecialchars($params['address1']);
            $from[] = '/{{ city }}/';
            $to[] = htmlspecialchars($params['city']);
            $from[] = '/{{ state }}/';
            $to[] = htmlspecialchars($params['state']);
            $from[] = '/{{ postcode }}/';
            $to[] = htmlspecialchars($params['postcode']);
            $from[] = '/{{ country }}/';
            $to[] = htmlspecialchars($params['country']);
            $from[] = '/{{ voice }}/';
            $to[] = htmlspecialchars($params['fullphonenumber']);
            $from[] = '/{{ email }}/';
            $to[] = htmlspecialchars($params['email']);
            $from[] = '/{{ orgcode }}/';
            $to[] = htmlspecialchars($params['orgcode']);
            $from[] = '/{{ clTRID }}/';
            $microtime = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-contact-update-' . $microtime);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <update>
      <contact:update xmlns:contact="http://www.domreg.lt/epp/xml/domreg-contact-1.1">
        <contact:id>{{ id }}</contact:id>
        <contact:chg>
          <contact:postalInfo type="{{ type }}">
            <contact:name>{{ name }}</contact:name>
            <contact:org>{{ org }}</contact:org>
            <contact:addr>
              <contact:street>{{ street1 }}</contact:street>
              <contact:city>{{ city }}</contact:city>
              <contact:sp>{{ state }}</contact:sp>
              <contact:pc>{{ postcode }}</contact:pc>
              <contact:cc>{{ country }}</contact:cc>
            </contact:addr>
          </contact:postalInfo>
          <contact:voice>{{ voice }}</contact:voice>
          <contact:email>{{ email }}</contact:email>
          <contact:orgcode>{{ orgcode }}</contact:orgcode>
        </contact:chg>
      </contact:update>
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
     * contactUpdateStatus
     */
    public function contactUpdateStatus($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        throw new EppException("Contact update status is not supported!");
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

        $return = array();
        try {
            $from = $to = array();
            $from[] = '/{{ id }}/';
            $to[] = htmlspecialchars($params['contact']);
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-contact-delete-' . $clTRID);
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
 <command>
   <delete>
     <contact:delete
      xmlns:contact="http://www.domreg.lt/epp/xml/domreg-contact-1.1">
       <contact:id>{{ id }}</contact:id>
     </contact:delete>
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
     * contactTransfer
     */
    public function contactTransfer($params = array())
    {
        if (!$this->isLoggedIn) {
            return array(
                'code' => 2002,
                'msg' => 'Command use error'
            );
        }

        throw new EppException("Contact transfer is not supported!");
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
                $text .= '<domain:name>' . $name . '</domain:name>' . "\n";
            }
            $from[] = '/{{ names }}/';
            $to[] = $text;
            $from[] = '/{{ clTRID }}/';
            $microtime = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-check-' . $microtime);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <check>
      <domain:check
        xmlns:domain="http://www.domreg.lt/epp/xml/domreg-domain-1.2">
        {{ names }}
      </domain:check>
    </check>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('http://www.domreg.lt/epp/xml/domreg-domain-1.2')->chkData;

            $i = 0;
            foreach ($r->cd as $cd) {
                $i++;
                $domains[$i]['name'] = (string)$cd->name;
                $availStr = (string)$cd->name->attributes()->avail;
                $domains[$i]['avail'] = ($availStr === 'true' || $availStr === '1') ? true : false;
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

        throw new EppException("Claims extension is not supported!");
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
            $extXml = '';
            if (!empty($params['authinfo'])) {
                $action = $params['authinfo'];

                // allow true => request
                if ($action === true) {
                    $action = 'request';
                }

                if ($action === 'request' || $action === 'cancel') {
                    $extXml = '<extension>
              <authInfo:info xmlns:authInfo="http://www.domreg.lt/epp/xml/authInfo-1.0">
                <authInfo:' . $action . '/>
              </authInfo:info>
            </extension>';
                }
            }
            $from[] = '/{{ extension }}/';
            $to[]   = $extXml;
            $from[] = '/{{ clTRID }}/';
            $microtime = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-info-' . $microtime);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <info>
      <domain:info
       xmlns:domain="http://www.domreg.lt/epp/xml/domreg-domain-1.2">
        <domain:name>{{ domainname }}</domain:name>
      </domain:info>
    </info>
    {{ extension }}
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $resp = $this->writeRequest($xml);
            $r = $resp;
            $authValidUntil = '';
            if (isset($resp->response->extension)) {
                $ext = $resp->response->extension
                    ->children('http://www.domreg.lt/epp/xml/authInfo-1.0');

                if (isset($ext->infData->validUntil)) {
                    $authValidUntil = (string)$ext->infData->validUntil;
                }
            }
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('http://www.domreg.lt/epp/xml/domreg-domain-1.2')->infData;
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
                $contact[$i]['id'] = (string)$e;
            }
            $ns = array();
            $i = 0;
            foreach ($r->ns->hostAttr as $hostAttr) {
                $i++;
                $ns[$i] = (string)$hostAttr;
            }
            $host = array();
            $i = 0;
            foreach ($r->host as $hostname) {
                $i++;
                $host[$i] = (string)$hostname;
            }
            $crDate = (string)$r->crDate;
            $upDate = (string)$r->upDate;
            $exDate = (string)$r->exDate;
            $trDate = (string)$r->trDate;
            $authPw = '';
            if (isset($r->authInfo->pw)) {
                $authPw = (string)$r->authInfo->pw;
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
                'crDate' => $crDate,
                'upDate' => $upDate,
                'exDate' => $exDate,
                'trDate' => $trDate,
                'authInfoPw' => $authPw,
                'authInfoValidUntil' => $authValidUntil
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
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-info-' . $clTRID);
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
    <epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
      <command>
        <info>
          <domain:info
           xmlns:domain="http://www.domreg.lt/epp/xml/domreg-domain-1.2">
            <domain:name>{{ name }}</domain:name>
          </domain:info>
        </info>
        <clTRID>{{ clTRID }}</clTRID>
      </command>
    </epp>');
            $r = $this->writeRequest($xml);
            $r = $r->response->resData->children('http://www.domreg.lt/epp/xml/domreg-domain-1.2')->infData;

            $currentNs = array();
            foreach ($r->ns->hostAttr as $hostAttr) {
                $hostName = (string)$hostAttr->hostName;
                $ipv4 = '';
                $ipv6 = '';

                foreach ($hostAttr->hostAddr as $hostAddr) {
                    $ipType = (string)$hostAddr->attributes()->ip;
                    if ($ipType === 'v4') {
                        $ipv4 = (string)$hostAddr;
                    } elseif ($ipType === 'v6') {
                        $ipv6 = (string)$hostAddr;
                    }
                }

                $currentNs[$hostName] = array_filter([
                    'hostName' => $hostName,
                    'ipv4' => $ipv4,
                    'ipv6' => $ipv6
                ]);
            }
            
            $add = $rem = array();
            foreach ($params['nss'] as $ns) {
                if (is_array($ns)) {
                    $hostName = $ns['hostName'];
                    $ipv4 = $ns['ipv4'] ?? '';
                    $ipv6 = $ns['ipv6'] ?? '';
                    $nsKey = $hostName . ($ipv4 ? "|v4:$ipv4" : '') . ($ipv6 ? "|v6:$ipv6" : '');

                    if (!isset($currentNs[$hostName]) || $currentNs[$hostName] != $ns) {
                        $add[$nsKey] = $ns;
                    }
                } else {
                    // Handle simple hostObj case
                    if (!isset($currentNs[$ns])) {
                        $add[$ns] = ['hostName' => $ns];
                    }
                }
            }
            
            foreach ($currentNs as $hostName => $nsData) {
                if (!in_array($hostName, array_column($params['nss'], 'hostName'))) {
                    $rem[$hostName] = $nsData;
                }
            }

            if (!empty($add) || !empty($rem)) {
                $from = $to = array();
                $addXml = '';
                foreach ($add as $ns) {
                    $addXml .= '<domain:hostAttr>';
                    $addXml .= '<domain:hostName>' . htmlspecialchars($ns['hostName']) . '</domain:hostName>';
                    if (!empty($ns['ipv4'])) {
                        $addXml .= '<domain:hostAddr ip="v4">' . htmlspecialchars($ns['ipv4']) . '</domain:hostAddr>';
                    }
                    if (!empty($ns['ipv6'])) {
                        $addXml .= '<domain:hostAddr ip="v6">' . htmlspecialchars($ns['ipv6']) . '</domain:hostAddr>';
                    }
                    $addXml .= '</domain:hostAttr>' . "\n";
                }

                $from[] = '/{{ add }}/';
                $to[] = (empty($addXml) ? '' : "<domain:add><domain:ns>\n{$addXml}</domain:ns></domain:add>\n");

                $remXml = '';
                foreach ($rem as $ns) {
                    $remXml .= '<domain:hostAttr>';
                    $remXml .= '<domain:hostName>' . htmlspecialchars($ns['hostName']) . '</domain:hostName>';
                    if (!empty($ns['ipv4'])) {
                        $remXml .= '<domain:hostAddr ip="v4">' . htmlspecialchars($ns['ipv4']) . '</domain:hostAddr>';
                    }
                    if (!empty($ns['ipv6'])) {
                        $remXml .= '<domain:hostAddr ip="v6">' . htmlspecialchars($ns['ipv6']) . '</domain:hostAddr>';
                    }
                    $remXml .= '</domain:hostAttr>' . "\n";
                }

                $from[] = '/{{ rem }}/';
                $to[] = (empty($remXml) ? '' : "<domain:rem><domain:ns>\n{$remXml}</domain:ns></domain:rem>\n");
                $from[] = '/{{ name }}/';
                $to[] = htmlspecialchars($params['domainname']);
                $from[] = '/{{ clTRID }}/';
                $clTRID = str_replace('.', '', round(microtime(1), 3));
                $to[] = htmlspecialchars($this->prefix . '-domain-updateNS-' . $clTRID);
                $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
    <epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
      <command>
        <update>
          <domain:update
           xmlns:domain="http://www.domreg.lt/epp/xml/domreg-domain-1.2">
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

        $return = array();
        try {
            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            if ($params['contacttype'] === 'registrant') {
                $from[] = '/{{ add }}/';
                $to[] = "";
                $from[] = '/{{ rem }}/';
                $to[] = "";
                $from[] = '/{{ chg }}/';
                $to[] = "<domain:chg><domain:registrant>".htmlspecialchars($params['new_contactid'])."</domain:registrant></domain:chg>\n";
            } else {
                $from[] = '/{{ add }}/';
                $to[] = "<domain:add><domain:contact>".htmlspecialchars($params['new_contactid'])."</domain:contact></domain:add>\n";
                $from[] = '/{{ rem }}/';
                $to[] = "<domain:rem><domain:contact>".htmlspecialchars($params['old_contactid'])."</domain:contact></domain:rem>\n";
                $from[] = '/{{ chg }}/';
                $to[] = "";    
            }
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-updateContact-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
 <command>
   <update>
     <domain:update
           xmlns:domain="http://www.domreg.lt/epp/xml/domreg-domain-1.2">
       <domain:name>{{ name }}</domain:name>
       {{ add }}
       {{ rem }}
       {{ chg }}
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

        throw new EppException("Domain status update is not supported!");
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

        throw new EppException("Domain authinfo update is not supported!");
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
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
 <command>
   <update>
     <domain:update
       xmlns:domain="http://www.domreg.lt/epp/xml/domreg-domain-1.2">
       <domain:name>{{ name }}</domain:name>
     </domain:update>
   </update>
   <extension>
     <secDNS:update
       xmlns:secDNS="urn:ietf:params:xml:ns:secDNS-1.1">
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
            $from = $to = array();
            $from[] = '/{{ name }}/';
            $to[] = htmlspecialchars($params['domainname']);
            $op = htmlspecialchars((string)($params['op'] ?? ''));
            if ($op !== 'request') {
                throw new EppException("Unsupported value for transfer:op: '{$op}'. Only 'request' is allowed.");
            }
            $from[] = '/{{ trType }}/';
            $to[]   = isset($params['trType']) ? htmlspecialchars($params['trType']): '';
            $from[] = '/{{ registrant }}/';
            $to[]   = isset($params['registrant']) ? htmlspecialchars($params['registrant']): '';
            $from[] = '/{{ tech }}/';
            $to[]   = isset($params['tech']) ? htmlspecialchars($params['tech']): '';
            $authInfoXml = '';
            if (!empty($params['authInfoPw'])) {
                $authInfoXml = "<domain:authInfo>\n<domain:pw>" .
                    htmlspecialchars((string)$params['authInfoPw']) .
                    "</domain:pw>\n</domain:authInfo>";
            }
            $from[] = '/{{ authInfo }}/';
            $to[]   = $authInfoXml;
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-transfer-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
            <epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
              <command>
                <transfer op="request">
                  <domain:transfer
                   xmlns:domain="http://www.domreg.lt/epp/xml/domreg-domain-1.2">
                    <domain:name>{{ name }}</domain:name>
                    <domain:trType>{{ trType }}</domain:trType>
                    {{ authInfo }}
                    <domain:ns/>
                    <domain:registrant>{{ registrant }}</domain:registrant>
                    <domain:contact>{{ tech }}</domain:contact>
                  </domain:transfer>
                </transfer>
                <clTRID>{{ clTRID }}</clTRID>
              </command>
            </epp>');           
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('http://www.domreg.lt/epp/xml/domreg-domain-1.2')->trnData;
            $name = (string)($r->name ?? 'N/A');

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

            $text = '';
            if (!empty($params['nss']) && is_array($params['nss'])) {
                foreach ($params['nss'] as $hostObj) {
                    $text .= "<domain:hostAttr>\n";
                    if (!empty($hostObj['hostName'])) {
                        $text .= '  <domain:hostName>' . htmlspecialchars($hostObj['hostName']) . "</domain:hostName>\n";
                    }
                    if (!empty($hostObj['ipv4'])) {
                        $text .= '  <domain:hostAddr ip="v4">' . htmlspecialchars($hostObj['ipv4']) . "</domain:hostAddr>\n";
                    }
                    if (!empty($hostObj['ipv6'])) {
                        $text .= '  <domain:hostAddr ip="v6">' . htmlspecialchars($hostObj['ipv6']) . "</domain:hostAddr>\n";
                    }
                    $text .= "</domain:hostAttr>\n";
                }
            }
            $from[] = '/{{ hostObjs }}/';
            $to[]   = $text;

            $from[] = '/{{ registrant }}/';
            $to[]   = isset($params['registrant']) ? htmlspecialchars($params['registrant']): '';
            $from[] = '/{{ tech }}/';
            $to[]   = isset($params['tech']) ? htmlspecialchars($params['tech']): '';
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-create-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <create>
      <domain:create
       xmlns:domain="http://www.domreg.lt/epp/xml/domreg-domain-1.2">
        <domain:name>{{ name }}</domain:name>
        <domain:period unit="y">{{ period }}</domain:period>
        <domain:ns>
          {{ hostObjs }}
        </domain:ns>
        <domain:registrant>{{ registrant }}</domain:registrant>
        <domain:contact>{{ tech }}</domain:contact>
      </domain:create>
    </create>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $r = $r->response->resData->children('http://www.domreg.lt/epp/xml/domreg-domain-1.2')->creData;
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

            $text = '';
            if (!empty($params['nss']) && is_array($params['nss'])) {
                foreach ($params['nss'] as $hostObj) {
                    $text .= "<domain:hostAttr>\n";
                    if (!empty($hostObj['hostName'])) {
                        $text .= '  <domain:hostName>' . htmlspecialchars($hostObj['hostName']) . "</domain:hostName>\n";
                    }
                    if (!empty($hostObj['ipv4'])) {
                        $text .= '  <domain:hostAddr ip="v4">' . htmlspecialchars($hostObj['ipv4']) . "</domain:hostAddr>\n";
                    }
                    if (!empty($hostObj['ipv6'])) {
                        $text .= '  <domain:hostAddr ip="v6">' . htmlspecialchars($hostObj['ipv6']) . "</domain:hostAddr>\n";
                    }
                    $text .= "</domain:hostAttr>\n";
                }
            }
            $from[] = '/{{ hostObjs }}/';
            $to[]   = $text;

            $from[] = '/{{ registrant }}/';
            $to[]   = isset($params['registrant']) ? htmlspecialchars($params['registrant']): '';
            $from[] = '/{{ tech }}/';
            $to[]   = isset($params['tech']) ? htmlspecialchars($params['tech']): '';
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
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-domain-createDNSSEC-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <create>
      <domain:create
       xmlns:domain="http://www.domreg.lt/epp/xml/domreg-domain-1.2">
        <domain:name>{{ name }}</domain:name>
        <domain:period unit="y">{{ period }}</domain:period>
        <domain:ns>
          {{ hostObjs }}
        </domain:ns>
        <domain:registrant>{{ registrant }}</domain:registrant>
        <domain:contact>{{ tech }}</domain:contact>
      </domain:create>
    </create>
    <extension>
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
            $r = $r->response->resData->children('http://www.domreg.lt/epp/xml/domreg-domain-1.2')->creData;
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

        throw new EppException("Claims extension is not supported!");
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

        throw new EppException("Sunrise is not supported!");
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
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <info>
      <domain:info
       xmlns:domain="http://www.domreg.lt/epp/xml/domreg-domain-1.2">
        <domain:name>{{ name }}</domain:name>
      </domain:info>
    </info>
    <clTRID>{{ clTRID }}</clTRID>
  </command>
</epp>');
            $r = $this->writeRequest($xml);
            $r = $r->response->resData->children('http://www.domreg.lt/epp/xml/domreg-domain-1.2')->infData;

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
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <renew>
      <domain:renew
       xmlns:domain="http://www.domreg.lt/epp/xml/domreg-domain-1.2">
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
            $r = $r->response->resData->children('http://www.domreg.lt/epp/xml/domreg-domain-1.2')->renData;
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
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <delete>
      <domain:delete
       xmlns:domain="http://www.domreg.lt/epp/xml/domreg-domain-1.2">
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

        throw new EppException("Domain restore is not supported!");
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

        throw new EppException("Domain report is not supported!");
    }

    /**
     * pollReq
     */
    public function pollReq()
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
            $from[] = '/{{ clTRID }}/';
            $clTRID = str_replace('.', '', round(microtime(1), 3));
            $to[] = htmlspecialchars($this->prefix . '-poll-req-' . $clTRID);
            $from[] = "/<\w+:\w+>\s*<\/\w+:\w+>\s+/ims";
            $to[] = '';
            $xml = preg_replace($from, $to, '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
   <epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
     <command>
       <poll op="req"/>
       <clTRID>{{ clTRID }}</clTRID>
     </command>
   </epp>');
            $r = $this->writeRequest($xml);
            $code = (int)$r->response->result->attributes()->code;
            $msg = (string)$r->response->result->msg;
            $messages = (int)($r->response->msgQ->attributes()->count ?? 0);
            $last_id = (int)($r->response->msgQ->attributes()->id ?? 0);
            $qDate = (string)($r->response->msgQ->qDate ?? '');
            $last_msg = (string)($r->response->msgQ->msg ?? '');
            $event = [];
            if (isset($r->response->resData)) {
                $ev = $r->response->resData->children('http://www.domreg.lt/epp/xml/domreg-event-1.0')->eventData;
                if ($ev) {
                    $event = [
                        'obType'  => (string)($ev->obType ?? ''),
                        'object'  => (string)($ev->object ?? ''),
                        'notice'  => (string)($ev->notice ?? ''),
                    ];
                }
            }

            $return = array(
                'code' => $code,
                'msg' => $msg,
                'messages' => $messages,
                'last_id' => $last_id,
                'qDate' => $qDate,
                'last_msg' => $last_msg,
                'event' => $event
            );
        } catch (\Exception $e) {
            $return = array(
                'error' => $e->getMessage()
            );
        }

        return $return;
    }

}