# Changelog

All notable changes to the **Armoris x402 Agent Payment** plugin will be documented in this file.

This project adheres to [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) and [Semantic Versioning](https://semver.org/).

---

## [0.1.7] - 2026-03-14



## [0.1.6] - 2026-03-10

- Minor bug fixes and maintenance.
- Prepared plugin for WordPress.org review submission.

## [0.1.3] - 2026-02-26

- Improved REST API reliability for agent discovery.
- Performance optimizations for product catalog fetching.

## [0.1.2] - 2026-02-25

- Fixes for variable product attribute matching.
- Updated metadata tags for better agent compatibility.

## [0.1.1] - 2026-02-25

- Minor documentation updates and localized string fixes.



## [0.1.0] - 2025-02-24

### Added
- **Agent Discovery** — Injects `x402:store_id`, `x402:gateway_url`, and `x402:context_url` meta tags into `<head>` so AI agents can discover the store and its gateway endpoint.
- **Quote Endpoint** (`POST /wp-json/x402/v1/quote`) — Returns a real-time cart quote including line item totals, tax, and shipping for a given set of SKUs and customer address.
- **Order Creation Endpoint** (`POST /wp-json/x402/v1/order`) — Allows the Armoris gateway to create a WooCommerce order on behalf of an AI agent after payment settlement.
- **Order Status Endpoint** (`GET /wp-json/x402/v1/order/{id}`) — Returns order status, line items, billing/shipping address, and on-chain transaction metadata.
- **Product Catalog Endpoint** (`GET /wp-json/x402/v1/products`) — Exposes product listings (with variation support) for agent-side product browsing.
- **Store Context Endpoint** (`GET /wp-json/x402/v1/context`) — Returns store metadata: name, currency, weight/dimension units, shipping countries, and featured products.
- **Active Currency Endpoint** (`GET /wp-json/x402/v1/active-currency`) — Returns the store's current WooCommerce currency code.
- **Client Secret Authentication** — All write and read endpoints (except `/context`) require a valid `X-402-Client-Secret` HTTP header.
- **Variable Product Support** — Quote endpoint resolves variable product variations using a multi-strategy attribute key matching approach (`attribute_`, `attribute_pa_`, case-insensitive fallback).
- **WooCommerce Payment Gateway** — Registers "Armoris x402 Agent Payment" as a payment method under WooCommerce Settings > Payments, configurable with Store ID and Client Secret.

---

## [Unreleased]

- Improved error messages and agent-facing error codes.
- Support for additional tokens (e.g., USDT, DAI).
- Webhook support for real-time payment confirmation callbacks.
- Admin dashboard widget showing recent x402 agent transactions.
