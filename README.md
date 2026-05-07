# Sheepy - PrestaShop Payment Module

![PrestaShop Version](https://img.shields.io/badge/PrestaShop-1.7%2B-blue)
![PHP Version](https://img.shields.io/badge/PHP-7.2.3%2B-blue)
![License](https://img.shields.io/badge/license-AFL--3.0-green)

This module integrates the [Sheepy](https://www.sheepy.com) cryptocurrency payment gateway into PrestaShop. Customers are redirected to a hosted Sheepy checkout to complete payment in the cryptocurrency of their choice, and the order status is updated automatically as the on-chain payment progresses.

Merchants can keep funds in cryptocurrency or set up automatic conversion to fiat or stablecoins to avoid FX risks.

## Supported cryptocurrencies

- [Bitcoin (BTC)](https://www.sheepy.com/supported-cryptocurrencies/bitcoin-btc)
- [Ethereum (ETH)](https://www.sheepy.com/supported-cryptocurrencies/ethereum-eth)
- Tether (USDT) on [TRC-20](https://www.sheepy.com/supported-cryptocurrencies/tether-trc-20), [ERC-20](https://www.sheepy.com/supported-cryptocurrencies/tether-erc-20-usdt), [BEP-20](https://www.sheepy.com/supported-cryptocurrencies/tether-bep-20-usdt)
- [USD Coin (USDC)](https://www.sheepy.com/supported-cryptocurrencies/usd-coin-erc-20) on ERC-20
- [TRON (TRX)](https://www.sheepy.com/supported-cryptocurrencies/tron-trx)
- [BNB](https://www.sheepy.com/supported-cryptocurrencies/binance-smart-chain-bnb) on Binance Smart Chain
- [Solana (SOL)](https://www.sheepy.com/supported-cryptocurrencies/solana-sol)
- [Litecoin (LTC)](https://www.sheepy.com/supported-cryptocurrencies/litecoin-ltc)
- [XRP](https://www.sheepy.com/supported-cryptocurrencies/xrp)
- [Bitcoin Cash (BCH)](https://www.sheepy.com/supported-cryptocurrencies/bitcoin-cash-bch)
- [Dogecoin (DOGE)](https://www.sheepy.com/supported-cryptocurrencies/dogecoin-doge)

The full list of supported currencies and trading pairs is available via the Sheepy API. See [Supported cryptocurrencies](https://www.sheepy.com/supported-cryptocurrencies) for the up-to-date list.

## Requirements

- PrestaShop **1.7** or higher
- PHP **7.2.3** or higher
- PHP extensions: `curl`, `json`, `mbstring`
- An active Sheepy merchant account with API credentials — [apply for an account](https://my.sheepy.com/auth/sign-up) or [contact us](https://www.sheepy.com/contact-us)
- HTTPS-enabled storefront (required for webhook delivery)

## Installation

1. Download `sheepy.zip` from the [Releases](https://github.com/sheepycrypto/sheepy-prestashop-plugin/releases) page.
2. In the PrestaShop back office, go to **Modules → Module Manager → Upload a module**.
3. Upload `sheepy.zip` and click **Install**.
4. Once installed, click **Configure** to enter your API credentials.

> The module folder must be named `sheepy` for the plugin to work properly.

## Configuration

The module assumes you already have a Sheepy merchant account (registration is not self-service).

1. Log in to the Sheepy dashboard at [my.sheepy.com](https://my.sheepy.com).
2. Go to **Integration → API Integration** and copy your **API Key** and **API Secret Key**.
3. Go to **Integration → Integration settings → Webhooks**:
   - Click **Generate secret key** and copy the resulting **Notification Secret Key**. It is shown only once.
   - The webhook URL field can be left empty — the module sends its callback URL with every invoice automatically.
4. In the PrestaShop back office, open **Modules → Module Manager → Sheepy → Configure**.
5. Paste the **API Key**, **API Secret Key**, and **Notification Secret Key**, then save.

After saving, Sheepy will appear as a payment option on the checkout page.

## Order statuses

The module installs five custom PrestaShop order statuses to track the crypto payment lifecycle:

| Status                                  | Meaning                                                       |
| --------------------------------------- | ------------------------------------------------------------- |
| Awaiting Sheepy payment                 | Order created, customer redirected to the Sheepy checkout     |
| Awaiting Sheepy payment confirmations   | On-chain transaction seen, waiting for confirmations          |
| Sheepy partial payment received         | The customer paid less than the invoice amount                |
| Sheepy payment expired                  | Invoice expired without payment                               |
| Sheepy payment is invalid               | Payment validation failed                                     |

On a successful payment, the order moves to PrestaShop's standard **Payment accepted** status. On error, it moves to **Payment error**.

## Changelog

### 1.0.3
- PrestaShop addon validation fixes
- Updated Composer dependencies
- Declared module dependencies for the PrestaShop addon catalog

### 1.0.2
- Minor fixes

### 1.0.1
- Minor fixes

### 1.0.0
- Initial release

## Support

For issues with the module, please open a ticket on the [GitHub Issues](https://github.com/sheepycrypto/sheepy-prestashop-plugin/issues) page.

For general Sheepy questions and merchant onboarding, visit [www.sheepy.com](https://www.sheepy.com) or [contact us](https://www.sheepy.com/contact-us). The Sheepy API reference is available at [www.sheepy.com/api](https://www.sheepy.com/api).

## License

This module is released under the [Academic Free License v3.0 (AFL-3.0)](https://opensource.org/licenses/AFL-3.0).
