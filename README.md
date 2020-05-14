# Predict by DPD module v1.0. Works with Thelia >= 2.1.0
# If you want to use it with Thelia 2.0 see [branch 2.0](https://github.com/thelia-modules/Predict/tree/2.0)

##Summary
[Français (fr_FR)](#fr_FR)

1. [Installation](#fr_FR_Install)
2. [Configuration](#fr_FR_Configure)
3. [Les boucles](#fr_FR_Loops)
4. [Intégration](#fr_FR_Integration)

[English (en_US)](#en_US)

1. [How to install](#en_US_Install)
2. [Configure the module](#en_US_Configure)
3. [The loops](#en_US_Loops)
4. [Integration](#en_US_Integration)

##fr_FR <a name="fr_FR"></a>

### Installation <a name="fr_FR_Install"></a>

#### Manuellement

Ce module doit être dans votre dossier ```modules/``` (thelia/local/modules/).

Vous pouvez télécharger le .zip ou créer un submodule git dans votre projet:
```
cd /path-to-thelia
git submodule add https://github.com/thelia-modules/Predict.git local/modules/Predict
```
#### Via composer

```
composer require thelia/dpd-predict-module:~1.0.1
```

Puis, allez dans votre Back-office Thelia pour activer ce module.

### Configuration <a name="fr_FR_Configure"></a>

Pour pouvoir utiliser ce module, vous devez d'abord entrer votre numéro de compte DPD dans
la page de configuration du module Predict, ainsi qu'optionnellement votre numéro de téléphone
mobile et cocher l'option Predict SMS.
N'oubliez pas d'assigner les zones de livraison au module Predict, ainsi que de configurer l'adresse de votre magasin,
ceci est indispensable pour utiliser ce module.

### Les boucles <a name="fr_FR_Loops"></a>

Le module Predict fourni trois boucles :

- predict.check.rights

- predict.notsend.loop

- predict

Vérifier si thelia peut lire et écrire dans le dossier Config et le fichier prices.json
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

Obtenir toutes les commandes payées et non envoyées dont le module de livraison est Predict
```html
{loop name="get.predict.orders" type="predict.notsend.loop"}
    ...
{/loop}
```

Obtenir les tarifs d'une zone de livraison
```html
{loop name="predict.prices" type="predict" area="1"}
    ...
{/loop}
```

### Intégration <a name="fr_FR_Integration"></a>

Le module utilise les hooks ```order-delivery.stylesheet``` et ```order-delivery.extra```, l'intégration avec le module est donc déja effectuée.

Si vous souhaitez personnalisé l'intégration, reportez-vous à la [documentation](http://doc.thelia.net/en/documentation/modules/hooks/hook_create.html#use-smarty-template-in-hooks)

##en_US <a name="en_US"></a>

### How to install <a name="en_US_Install"></a>

#### Manually

This module must be into your ```modules/``` directory (thelia/local/modules/).

You can download the .zip file of this module or create a git submodule into your project like this :

```
cd /path-to-thelia
git submodule add https://github.com/thelia-modules/Predict.git local/modules/Predict
```
#### Using composer

```
composer require thelia/dpd-predict-module:~1.0.1
```

Next, go to your Thelia admin panel for module activation.

### Configure the module <a name="en_US_Configure"></a>

Before using this module you first need to configure your DPD account number,
and optionally your cellphone number and check if you have the Predict SMS option.
Don't forget to assign the shipping zones to the Predict module and to configure your store's address,
it's necessary in order to use this module.

### The loops <a name="en_US_Loops"></a>

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

### Integration <a name="en_US_Integration"></a>

This module uses the Hooks ```order-delivery.stylesheet``` and ```order-delivery.extra```, the integration with the default template is already done.

If you want to custom the integration, you can see how to do that in the [documentation](http://doc.thelia.net/en/documentation/modules/hooks/hook_create.html#use-smarty-template-in-hooks)
