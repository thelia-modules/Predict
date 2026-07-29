# Predict

Predict by DPD module ~2.1.0 Works with Thelia >= 2.5.2    
If you want to use it with Thelia <= 2.5.2 see Predict ~2.0.0

## Installation

```
composer require thelia/predict-module ^2.1
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

**Front-office: Flexy only (Thelia 3).**

The Thelia 2 Smarty hooks `order-delivery.stylesheet` and `order-delivery.extra` no longer
exist. `Hook/PredictHook.php`, `templates/frontOffice/default/predict.html` and its stylesheet
were removed during the Thelia 3 migration: the hook had lost its `config.xml` registration and
was never ported to `getSubscribedHooks()`, so those files had become unreachable — a Smarty
theme silently lost the cellphone field at checkout.

The cellphone number is now handled by the theme, through two routes:

| Route | Purpose |
|---|---|
| `GET /predict/cellphone/value` | `{"cellphone": "…"}` — session value, else the customer's default address |
| `POST /predict/cellphone/save` | Stores the number in session |

`POST /predict/cellphone/save` requires the Thelia CSRF token as `_token`, and rejects input
that is not phone-shaped (digits, spaces, dots, dashes, brackets, optional leading `+`, 30
characters max). From a Flexy template, take the token from `{{ assignToken() }}`:

```js
await fetch('/predict/cellphone/save', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams({predict_cellphone: value, _token: token}),
})
```

Routes rather than a module-provided Twig function on purpose: a theme template that calls a
module function fails to compile when the module is disabled, taking the whole delivery step
with it.

**Restoring Smarty support** would mean adding `getSubscribedHooks()` to a new front hook and
bringing back the two template files — nothing else in the module depends on them.

### Back-office

Rendered by the `default-twig` theme (`templates/backOffice/default-twig/Predict/`). Form and
export errors are reported through the flash bag, which survives the redirect these actions end
with — `ParserContext` does not, and is kept only for a Thelia 2 Smarty back-office.
