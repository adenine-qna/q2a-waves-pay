# q2a-waves-pay
Waves payment plugin for [Question2Answer](https://wavesplatform.com/) (Q2A) which is a free and open source platform for Q&A sites.

Waves-pay is a supporting plugin for other Q2A plugins that want to utilize the [Waves Payments API](https://docs.wavesplatform.com/en/development-and-api/client-api/payments-api.html).

## Features

- Configure a Waves account address to receive all payments.
- Configure a list of Waves assets acceptable as payments.
- Show all payments in a paged table.
- Wrap the Waves payment API call with a simpler function.
- Wrap the Waves transaction verification with a simpler function.
- Record all payments into a database table.

## Installation

- Download and extract the files into the Q2A plugin folder *qa-plugin/waves-pay*.
- In the Q2A *Admin* menu click *Plugins* sub-menu.
- Tick the *Enabled* checkbox for the *Waves Pay* plugin.
- Click the *Save Options* button.
- There will be a red message saying *The Waves Payments Page page module requires some database initialization.*
- Click the *database initialization* link. An instruction page will open.
- Click the *Initialize the Database* button.
- Done. Click the *Go to admin center* link.

## Functions

1. Generate a Waves payment API request URL:
~~~PHP
function wp_get_waves_pay_req_url($asset, $amount, $cb_url);
~~~
where:
- *$asset* is the Waves asset name defined as an acceptable payment.
- *$amount* is the requested amount of the asset as payment.
- *$cb_url* is the call back URL provided by the deriving plugin.

The function will return a URL that when redirected will open a Waves client with the requested payment details. Please refer to the [Waves Payments API](https://docs.wavesplatform.com/en/development-and-api/client-api/payments-api.html) for more details.

2. Validate a Waves transaction:
~~~PHP
function wp_is_valid_payment($txid, $asset, $min_amount);
~~~
where:
- *$txid* is the Waves transaction ID.
- *$asset* is the Waves asset name defined as an acceptable payment.
- *$min_amount* is the minimum requested amount of the asset as payment.

The function will return TRUE when the transaction is valid.

3. Create a payment record:
~~~PHP
function wp_create_payment_rec($txid, $asset_name, $amount, $purpose);
~~~
where
- *$txid* is the Waves transaction ID.
- *$asset_name* is the Waves asset name defined as an acceptable payment.
- *$amount* is the requested amount of the asset as payment.
- *$purpose* is the descriptive purpose of the payment.

The function will return the record ID.
