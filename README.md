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

2. In your PHP code, include the Composer autoloader and use the EppClient class from the Pinga Tembo package:

```
// Include the Composer autoloader
require_once 'vendor/autoload.php';

// Use the Epp class from the Pinga Tembo package
use Pinga\Tembo\Epp;
use Pinga\Tembo\EppClient;
use Pinga\Tembo\HttpsClient;
use Pinga\Tembo\RRIClient;
```

3. You can now use the EppClient class and its functions in your code. You can refer to the **examples** directory for examples of how the package can be used.

## Supported EPP Commands

| | domain | contact | host | session |
|----------|----------|----------|----------|----------|
| check | ✅ | ✅ | ✅ | login ✅ |
| info | ✅ | ✅ | ✅ | logout ✅ |
| create | ✅ | ✅ | ✅ | poll 🚧 |
| update | N/A | ✅ | 🚧 | hello ✅ |
| updateNS | ✅ | N/A | N/A | keep-alive ✅ |
| updateContact | ✅ ❗ | N/A | N/A | |
| updateStatus | 🚧 | ❌ | ❌| |
| updateDNSSEC | 🚧 | N/A | N/A | |
| renew | ✅ | N/A | N/A | |
| delete | ✅ | ✅ | ✅ |  |
| rgp:restore | ✅ | N/A | N/A | |
| rgp:report | ✅ | N/A | N/A | |
| transferRequest | ✅ | ❌ | ❌ | |

## Supported Connection Types

| type | status |
|----------|----------|
| EPP over TLS/TCP | ✅ |
| EPP over HTTPS | ✅ |
| RRI | ✅ |
| REGRR | ❌ |

## Registry Support (28 backends and counting)

| Registry | TLDs | Extension | Status | TODO |
|----------|----------|----------|----------|----------|
| Generic RFC EPP | any | | ✅ | |
| AFNIC | .fr/others | afnic | 🚧 | work on extensions |
| Caucasus Online | .ge | | ✅ |  |
| CentralNic | all | | ✅ |  |
| CoCCA | all | | ✅ |  |
| CORE/Knipp | all | | ✅ |  |
| DENIC | .de | | ✅ | some functions need to be added |
| Domicilium | .im | | ✅ | small parsing fixes needed |
| DOMREG | .lt | domreg | 🚧 | work on extensions |
| FORTH-ICS | .gr, .ελ | forth | ✅ | work on transfers |
| FRED | .cz/any | fred | ✅ | domain update |
| GoDaddy Registry | all | | ✅ | |
| Google Nomulus | all | | ✅ | small parsing fixes needed |
| Hostmaster | .ua | ua | ✅ | |
| Identity Digital | all | | ✅ | |
| IIS | .se, .nu | iis.se | ✅ | transfer and domain contact update |
| HKIRC | .hk | | ✅ | test all commands |
| NASK | .pl | nask | ✅ | test all commands |
| NIC Chile | .cl | | 🚧 | further work needed |
| NORID | .no | norid | ✅ | transfer and update need testing |
| .PT | .pt | pt | 🚧 | work on extensions |
| Registro.it | .it | it | 🚧 | work on extensions |
| RoTLD | .ro | | ✅ | commands need further tests |
| RyCE | all | | ✅ | commands need further tests |
| SIDN | all | | ✅ | commands need further tests |
| Verisign | all | verisign | 🚧 | work on extensions |
| ZADNA | .za |  | ✅ | test all commands |
| ZDNS | all |  | ✅ | |
