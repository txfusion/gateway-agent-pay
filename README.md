# x402 Agent Gateway for WooCommerce

This plugin connects your WooCommerce store to the x402 Agent Payment Gateway, enabling AI Agents to discover your store, negotiate quotes, and perform payments using the x402 protocol.

## Features

- **Store Identity**: Links your WooCommerce store to your Agent Gateway Store ID.
- **Agent Discovery**: Injects necessary metadata into your store's pages so AI Agents can find the payment gateway and context.
- **x402 Payment Method**: Adds a dedicated payment method for agents to complete orders programmatically.

## Installation

1. Download this folder as a ZIP file (e.g., `woocommerce-x402.zip`).
2. Go to your WordPress Admin Dashboard.
3. Navigate to **Plugins > Add New**.
4. Click **Upload Plugin** and select the ZIP file.
5. Click **Install Now** and then **Activate**.

## Configuration

1. Go to **WooCommerce > Settings > Payments**.
2. Click on **x402 Agent Payment**.
3. Enable the payment method.
4. Enter your **Store ID** (found in your Agent Gateway Dashboard).
5. Enter the **Gateway URL** `http://api.armoris.io/`.
6. Save changes.

## Usage

Once configured, AI Agents visiting your site will detect the `x402-context` metadata and be able to interact with the Agent Gateway to process payments. When an order is created by an agent, it will appear in WooCommerce with the status "On Hold" (or "Processing" depending on gateway logic) and the payment method "x402".
