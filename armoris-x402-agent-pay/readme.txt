=== Armoris x402 Agent Pay ===
Contributors: armoris
Tags: woocommerce, payment gateway, ai agent, x402, usdc
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.1.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect your WooCommerce store to the Armoris x402 Gateway. Let AI agents browse, quote, and pay for products using USDC on-chain.

== Description ==

**Armoris x402 Agent Pay** bridges your WooCommerce store with the emerging AI-agent economy. Using the open [x402 protocol](https://x402.org), autonomous AI agents can discover your store, request price quotes, and settle payments directly on-chain — without human checkout friction.

= How it works =

1. Install and activate the plugin.
2. Connect your store to your [Armoris](https://armoris.io) merchant dashboard.
3. AI agents discover your store via injected meta tags and interact with the x402 REST endpoints to get quotes and create orders.
4. Payments are settled on-chain (USDC,...) and your WooCommerce orders are created automatically.

= Features =

* **Agent Discovery**: Injects `x402:store_id`, `x402:gateway_url`, and `x402:context_url` meta tags into your site's `<head>` for AI agent discovery.
* **Quote Endpoint**: Provides a secure REST endpoint (`POST /wp-json/x402/v1/quote`) so agents can get real-time price quotes including tax and shipping.
* **Automated Order Creation**: REST endpoint (`POST /wp-json/x402/v1/order`) creates WooCommerce orders when payment is confirmed by the gateway.
* **Order Status Lookup**: Agents can check order status via `GET /wp-json/x402/v1/order/{id}`.
* **Product Catalog Access**: Exposes product listings for agent browsing (`GET /wp-json/x402/v1/products`).
* **Store Context**: Provides store metadata (currency, shipping locations, featured products) to AI agents for informed purchasing decisions.
* **Client Secret Authentication**: All sensitive endpoints are protected by a shared secret between your store and the Armoris gateway.
* **Variable Product Support**: Handles variable products with attribute matching for AI agents.

= Requirements =

* WooCommerce 7.0 or higher
* An [Armoris](https://armoris.io) merchant account and Store ID
* PHP 7.4 or higher

== Installation ==

1. Upload/select the `woocommerce-x402` folder to the `/wp-content/plugins/` directory, or install the plugin through the Plugins screen directly.
2. Activate the plugin through the **Plugins** screen in your admin panel.
3. Navigate to **WooCommerce > Settings > Payments** and enable **x402 Agent Pay**.
4. Enter your **Store ID** and **Client Secret** from your [Armoris Dashboard](https://armoris.io/dashboard).
5. Set your **Armoris API Gateway URL** (default: `https://api.armoris.io`).
6. Save settings. Your store is now discoverable by AI agents.

== Frequently Asked Questions ==

= Do I need an Armoris account? =

Yes. The plugin acts as a bridge between your WooCommerce store and the Armoris x402 gateway. You need a merchant account at [armoris.io](https://armoris.io) to obtain your Store ID and Client Secret.

= Which blockchains and tokens are supported? =

Currently supported networks include SKALE Base Mainnet and Base (Ethereum L2), with USDC as the primary payment token. More networks will be added as the x402 ecosystem grows.

= Is this plugin compatible with other payment gateways? =

Yes. The x402 Agent Pay method coexists with other WooCommerce payment gateways. Human customers can still check out using standard methods (credit card, PayPal, etc.).

= Does this expose any sensitive data publicly? =

No. Store context metadata (name, currency, shipping locations, featured products) is public by design for agent discovery. All order creation, quote, and product endpoints require a valid Client Secret header (`X-402-Client-Secret`).

= What is the x402 protocol? =

x402 is an open protocol for machine-to-machine payments using HTTP. It extends the HTTP 402 "Payment Required" status code to enable autonomous agent commerce. See [x402.org](https://x402.org) for more details.

== Changelog ==

= 0.1.6 - 2026-03-10 =

= 0.1.3 - 2026-02-26 =

= 0.1.2 - 2026-02-25 =

= 0.1.1 - 2026-02-25 =

= 0.1.0 - 2025-02-24 =
**Added**
* Agent discovery via `x402:store_id`, `x402:gateway_url`, and `x402:context_url` meta tags injected into `<head>`.
* Secure REST endpoint `POST /wp-json/x402/v1/quote` for real-time price quotes (cart contents, tax, shipping).
* REST endpoint `POST /wp-json/x402/v1/order` for automated WooCommerce order creation from AI agents.
* REST endpoint `GET /wp-json/x402/v1/order/{id}` for agents to poll order and payment status.
* REST endpoint `GET /wp-json/x402/v1/products` for product catalog browsing by AI agents.
* REST endpoint `GET /wp-json/x402/v1/context` exposing store metadata (currency, shipping zones, featured products).
* REST endpoint `GET /wp-json/x402/v1/active-currency` for current store currency.
* Client Secret (`X-402-Client-Secret` header) authentication on all sensitive endpoints.
* Variable product support with multi-strategy attribute matching (`attribute_`, `attribute_pa_`, case-insensitive).
* WooCommerce payment gateway integration under **WooCommerce > Settings > Payments**.

== Upgrade Notice ==

= 0.1.0 =
Initial release. No upgrade steps required.

== Privacy Policy ==

This plugin does not store any personal data beyond what WooCommerce itself already handles. Order billing and shipping addresses are stored as part of standard WooCommerce orders. No data is sent to third-party services without merchant configuration (Armoris gateway URL).
