# Tembo EPP Client

[![StandWithUkraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/badges/StandWithUkraine.svg)](https://github.com/vshymanskyy/StandWithUkraine/blob/main/docs/README.md)

[![SWUbanner](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner2-direct.svg)](https://github.com/vshymanskyy/StandWithUkraine/blob/main/docs/README.md)

Welcome to our open source PHP EPP client!

Our client allows you to easily connect to EPP servers and manage domain registration and other EPP services. Whether you are using Pinga or any other PHP framework, our client integrates seamlessly to provide a flexible and powerful solution for managing your EPP needs.

Some of the key features of our client include:

- Support for multiple EPP extensions: Connect to a wide range of EPP servers and take advantage of various EPP services

- Easy integration: Integrates smoothly with Pinga or any other PHP framework

- Customizable configuration: Adjust settings to meet your specific needs and easily modify the client to work with any domain registry

- Advanced security: Protect your data with TLS encryption

- Open source and freely available: Use and modify our client as you see fit

Whether you are a developer looking to enhance your application with EPP functionality or a domain registrar seeking a reliable EPP client, our open source solution is the perfect choice. Join us and revolutionize your EPP management today!

## Installation

To install the Pinga Tembo EPP client, follow these steps:

1. Navigate to your project directory and run the following command:

```composer require pinga/tembo```

2. In your PHP code, include the **Connection.php** file from the Tembo package:

```
require_once 'Connection.php';
```

3. To create test certificates (cert.pem and key.pem), if the registry does not have mandatory SSL certificates, you can use:

```
openssl genrsa -out key.pem 2048
```

```
openssl req -new -x509 -key key.pem -out cert.pem -days 365
```

4. You can now use the EppClient class and its functions in your code. You can refer to the **examples** directory for examples of how the package can be used.

5. To test if you have access to the EPP server from your system, you may use:

```
openssl s_client -showcerts -connect epp.example.com:700
```

```
openssl s_client -connect epp.example.com:700 -CAfile cacert.pem -cert cert.pem -key key.pem
```

## Supported EPP Commands

| | domain | contact | host | session |
|----------|----------|----------|----------|----------|
| check | ✅ | ✅ | ✅ | login ✅ |
| checkClaims | ✅ | N/A | N/A | logout ✅ |
| info | ✅ | ✅ | ✅ | poll ✅ |
| create | ✅ | ✅ | ✅ | hello ✅ |
| createDNSSEC | ✅ | N/A | N/A | keep-alive ✅ |
| createClaims | ✅ | N/A | N/A | |
| update | N/A | ✅ | ✅ | |
| updateNS | ✅ | N/A | N/A | |
| updateContact | ✅ | N/A | N/A | |
| updateAuthinfo | ✅ | N/A | N/A | |
| updateStatus | ✅ | ❌ | ❌| |
| updateDNSSEC | ✅ | N/A | N/A | |
| renew | ✅ | N/A | N/A | |
| delete | ✅ | ✅ | ✅ |  |
| transferRequest | ✅ | ❌ | ❌ | |
| transferQuery | ✅ | ❌ | ❌ | |
| transferApprove | ✅ | ❌ | ❌ | |
| transferReject | ✅ | ❌ | ❌ | |
| transferCancel | ✅ | ❌ | ❌ | |
| rgp:restore | ✅ | N/A | N/A | |
| rgp:report | ✅ | N/A | N/A | |

## Supported Connection Types

| type | status |
|----------|----------|
| EPP over TLS/TCP | ✅ |
| EPP over HTTPS | ✅ |
| RRI | ✅ |
| REGRR | ❌ |

## Registry Support (30 backends and counting)

| Registry | TLDs | Extension | Status | TODO |
|----------|----------|----------|----------|----------|
| Generic RFC EPP | any | | ✅ | |
| AFNIC | .fr/others | FR | 🚧 | work on extensions |
| Caucasus Online | .ge | | ✅ |  |
| CentralNic | all | | ✅ |  |
| CoCCA | all | | ✅ |  |
| CORE/Knipp | all | | ✅ |  |
| DENIC | .de | | ✅ | some functions need to be added |
| Domicilium | .im | | ✅ | small parsing fixes needed |
| DOMREG | .lt | LT | 🚧 | work on extensions |
| FORTH-ICS | .gr, .ελ | GR | ✅ | |
| FRED | .cz/any | FRED | ✅ | domain update, DNSSEC |
| GoDaddy Registry | all | | ✅ | |
| Google Nomulus | all | | ✅ | small parsing fixes needed |
| Hostmaster | .ua | UA | ✅ | |
| Identity Digital | all | | ✅ | |
| IIS | .se, .nu | SE | ✅ | transfer and domain contact update |
| HKIRC | .hk | | ✅ | more tests |
| NASK | .pl | PL | ✅ | |
| NIC Chile | .cl | | 🚧 | further work needed |
| NIC.LV | .lv | LV | ✅ | |
| NORID | .no | NO | ✅ | |
| .PT | .pt | PT | ✅ | more tests |
| Registr.io | all | | ✅ | |
| Registro.it | .it | IT | 🚧 | work on extensions |
| RoTLD | .ro | | ✅ | more tests |
| RyCE | all | | ✅ | more tests |
| SIDN | all | | ✅ | more tests |
| Verisign | all | VRSN | 🚧 | work on extensions |
| ZADNA | .za |  | ✅ | more tests |
| ZDNS | all |  | ✅ | |

## Integration with billing systems

Would you like to see any registry added as a WHMCS/FOSSBilling module? Or an EPP module for any other billing system? Simply create an [issue](https://github.com/getpinga/tembo/issues) in this project and let us know.

### WHMCS

| Registry | TLDs | Status | Project |
|----------|----------|----------|----------|
| Generic RFC EPP | any | ✅ | [whmcs-epp-rfc](https://github.com/getpinga/whmcs-epp-rfc) |
| Hostmaster | .ua | ✅ | [whmcs-epp-ua](https://github.com/getpinga/whmcs-epp-ua) |

### FOSSBilling

| Registry | TLDs | Status | Project |
|----------|----------|----------|----------|
| Generic RFC EPP | any | ✅ | [fossbilling-epp-rfc](https://github.com/getpinga/fossbilling-epp-rfc) |
| Hostmaster | .ua | ✅ | [fossbilling-epp-ua](https://github.com/getpinga/fossbilling-epp-ua) |
| FRED | .cz/any | ✅ | [fossbilling-epp-fred](https://github.com/getpinga/fossbilling-epp-fred) |
