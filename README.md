# Predict

Predict by DPD module ~2.1.1 Works with Thelia >= 2.5.2    
If you want to use it with Thelia <= 2.5.2 see Predict ~2.0.0

## Installation

```
composer require thelia/predict-module ~2.1.1
```

## Usage

Before using this module you first need to configure your DPD account number,
and optionally your cellphone number and check if you have the Predict SMS option.
Don't forget to assign the shipping zones to the Predict module and to configure your store's address,
it's necessary in order to use this module.

### The loops

The Predict module brings you three loops:

- predict.check.rights

- predict.notsend.loop

- predict

Check if the Config folder and the prices.json file are readable and writable
```html
{loop name="predict.check.rights.loop" type="predict.check.rights"}
    <div class="alert alert-danger">
        {$ERRMES} {$ERRFILE}
    </div>
{/loop}

{elseloop rel="predict.check.rights.loop"}
    <!-- No error, we can continue -->
{/elseloop}
```

Get every order which are paid and not sent and has Predict as delivery module
```html
{loop name="get.predict.orders" type="predict.notsend.loop"}
    ...
{/loop}
```

Get the prices of a given area
```html
{loop name="predict.prices" type="predict" area="1"}
    ...
{/loop}
```

### Integration

This module uses the Hooks ```order-delivery.stylesheet``` and ```order-delivery.extra```, the integration with the default template is already done.

If you want to custom the integration, you can see how to do that in the [documentation](http://doc.thelia.net/en/documentation/modules/hooks/hook_create.html#use-smarty-template-in-hooks)
