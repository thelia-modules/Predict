#Predict by exapaq module v1.0

##Summary
Français (fr_FR)

1. Installation

2. Configuration

3. Les boucles

4. Intégration

English (en_US)

1. How to install

2. Configure the module

3. The loops

4. Integration

##fr_FR

### Installation
Ce module doit être dans votre dossier ```modules/``` (thelia/local/modules/).

Vous pouvez télécharger le .zip ou créer un submodule git dans votre projet:
```
cd /path-to-thelia
git submodule add https://github.com/thelia-modules/Predict.git local/modules/Predict
```

Puis, allez dans votre Back-office Thelia pour activer ce module.

### Configuration

Pour pouvoir utiliser ce module, vous devez d'abord entrer votre numéro de compte exapaq dans
la page de configuration du module Predict, ainsi qu'optionnellement votre numéro de téléphone
mobile et cocher l'option Predict SMS.

### Les boucles

Le module Predict fourni trois boucles :

- predict.check.rights

- predict.notsend.loop

- predict

__Vérifier si thelia peut lire et écrire dans le dossier Config et le fichier prices.json
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

__Obtenir toutes les commandes payées et non envoyées dont le module de livraison est Predict
```html
{loop name="get.predict.orders" type="predict.notsend.loop"}
    ...
{/loop}
```

__Obtenir les tarifs d'une zone de livraison
```html
{loop name="predict.prices" type="predict" area="1"}
    ...
{/loop}
```

### Intégration

Le module Predict à besoin de rajouter un champ pour entrer le téléphone mobile dans le formulaire
de selection du module de livraison, un exemple vous est fourni dans le fichier
```dossier-de-Predict/templates/frontOffice/default/ajax/order-delivery-module-list.html```

Pour l'utiliser, copiez le dans le dossier :
```dossier-de-Thelia/templates/frontOffice/default/ajax/```

##en_US

### How to install
This module must be into your ```modules/``` directory (thelia/local/modules/).

You can download the .zip file of this module or create a git submodule into your project like this :

```
cd /path-to-thelia
git submodule add https://github.com/thelia-modules/Predict.git local/modules/Predict
```

Next, go to your Thelia admin panel for module activation.

### Configure the module

Before using this module you first need to configure your exapaq account number,
and optionally your cellphone number and check if you have the Predict SMS option.

### The loops

The Predict module brings you three loops:

- predict.check.rights

- predict.notsend.loop

- predict

__Check if the Config folder and the prices.json file are readable and writable
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

__Get every order which are paid and not sent and has Predict as delivery module
```html
{loop name="get.predict.orders" type="predict.notsend.loop"}
    ...
{/loop}
```

__Get the prices of a given area
```html
{loop name="predict.prices" type="predict" area="1"}
    ...
{/loop}
```

### Integration

The Predict module needs to add a field for the cellphone in the delivery module list, we provide you
an example in the file:
```path-to-Predict/templates/frontOffice/default/ajax/order-delivery-module-list.html```

In order to use this functionality, copy this previous file in the directory :
```path-to-Thelia/templates/frontOffice/default/ajax/```