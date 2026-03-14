=== Armoris x402 Agent Pay ===
Contributors: armoris
Tags: woocommerce, payment gateway, ai agent, x402, usdc
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.1.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect your WooCommerce store to the Armoris x402 Gateway. Let AI agents browse, quote, and pay for products using USDC on-chain.

== Description ==

**Armoris x402 Agent Pay** allows your WooCommerce store to interact with autonomous AI agents. The plugin acts as a bridge, exposing specific pieces of your store's information to the Armoris Gateway so that AI agents can find products, get pricing, and make payments using the [x402 protocol](https://x402.org).

**How it works and Data Flow:**

This plugin creates several secure endpoints on your website. The Armoris Gateway connects to these endpoints to help AI agents shop at your store:

*   **Store Context**: Shares your store's name, currency, and shipping rules so agents know which countries you serve.
*   **Product Information**: Allows the gateway to see your product list (prices, descriptions, stock) so agents can browse and find items.
*   **Quote Calculation**: When an agent wants to buy, the gateway sends the items and location to your store. Your store then calculates the exact tax and shipping costs.
*   **Order Placement**: Once an on-chain payment is verified, the gateway sends the order info and payment proof to your store to create a standard WooCommerce order.
*   **Order Tracking**: Allows agents to check if their order has been shipped or processed.

All communication between the gateway and your store (except for the public store description) is secured using a "Client Secret" that you configure in your dashboard.

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

== External services ==

This plugin connects to the **Armoris Gateway API** (`https://api.armoris.io`) to enable AI agents to browse products, request price quotes, and process on-chain USDC payments at your WooCommerce store.

**What data is sent and when:**

* **On plugin activation / page load (public):** Your Store ID and the Armoris Gateway URL are embedded as HTML meta tags in every page of your store's frontend. This allows AI agents to discover your store via the x402 protocol. No authentication is required for this.
* **When an AI agent browses your store (authenticated):** Your store's name, currency, supported shipping countries, weight/dimension units, and a list of up to 5 featured products (ID, name, SKU, price, image URL) are returned to the Armoris Gateway. This endpoint is secured using your Client Secret.
* **When an AI agent requests a price quote (authenticated):** The Armoris Gateway sends the agent's requested items (SKU, quantity, attributes) and shipping destination (country, state, postcode, city) to your store. Your store responds with a calculated subtotal, tax, and shipping cost. Secured using your Client Secret.
* **When an on-chain payment is confirmed (authenticated):** The Armoris Gateway sends the final order details — line items, billing/shipping address, payment method, and on-chain metadata (transaction ID, chain ID, wallet address) — to your store, which creates a standard WooCommerce order. Secured using your Client Secret.
* **When an AI agent checks an order status (authenticated):** The Armoris Gateway queries your store for the status, totals, line items, billing/shipping address, and on-chain metadata of a specific order. Secured using your Client Secret.

No data is transmitted to the Armoris Gateway itself — all communication is **inbound** from the gateway to your store's REST API endpoints. Your store does not make outbound HTTP requests to Armoris.

This service is provided by Armoris: [Terms of Service](https://armoris.io/terms) | [Privacy Policy](https://armoris.io/privacy).

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

= 0.1.7 - 2026-03-14 =

= 0.1.6 - 2026-03-10 =
* Minor bug fixes and maintenance.
* Prepared plugin for WordPress.org review submission.

= 0.1.3 - 2026-02-26 =
* Improved REST API reliability for agent discovery.
* Performance optimizations for product catalog fetching.

= 0.1.2 - 2026-02-25 =
* Fixes for variable product attribute matching.
* Updated metadata tags for better agent compatibility.

= 0.1.1 - 2026-02-25 =
* Minor documentation updates and localized string fixes.

= 0.1.0 - 2025-02-24 =
* Initial release.
* Support for x402 protocol payments (USDC on Base/SKALE).
* Secure REST endpoints for Quotes, Orders, and Context.
* AI Agent discovery via meta tags.

== Upgrade Notice ==

= 0.1.0 =
Initial release. No upgrade steps required.

== Privacy Policy ==

This plugin does not store any personal data beyond what WooCommerce itself already handles. Order billing and shipping addresses are stored as part of standard WooCommerce orders. Data is sent to the Armoris gateway (Store ID, Client Secret) to facilitate machine-to-machine payments as configured in the plugin settings.

