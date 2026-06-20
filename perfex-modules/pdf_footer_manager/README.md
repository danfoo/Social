# PDF Footer Manager — module Perfex CRM

Configure **facilement le pied de page** des PDF de Perfex (factures, devis,
propositions, avoirs, contrats, paiements…) depuis une page d'admin, **sans
toucher au code core** et donc **sans rien perdre lors des mises à jour** de
Perfex.

Le footer est injecté automatiquement sur **chaque page** du PDF via le moteur
mPDF de Perfex (`SetHTMLFooter`).

## Installation

1. Copier le dossier `pdf_footer_manager/` dans le répertoire `modules/` de votre
   installation Perfex :

   ```
   modules/pdf_footer_manager/
   ```

   > ⚠️ C'est bien le **contenu** de `perfex-modules/pdf_footer_manager` de ce
   > dépôt qu'il faut placer dans `modules/pdf_footer_manager` côté Perfex.

2. Dans Perfex : **Setup → Modules**, repérer *PDF Footer Manager* et cliquer
   sur **Activate**. L'activation crée les options par défaut.

3. Aller dans **Setup → PDF Footer Manager** pour configurer le pied de page.

## Utilisation

- **Activer le pied de page PDF** : interrupteur général.
- **Pied de page global (HTML)** : appliqué à tous les documents. Vous pouvez
  coller du HTML simple (mPDF supporte un sous-ensemble de CSS *inline*).
- **Utiliser le pied de page global pour tous les documents** : si décoché, vous
  pouvez définir un pied de page **par type de document** (factures, devis…).
- **Marge basse** : espace réservé en bas de page pour le footer (en mm).

### Champs de fusion

Remplacés au rendu par les infos de la société Perfex :

`{company_name}` `{company_address}` `{company_city}` `{company_phone}`
`{company_email}` `{company_vat}` `{website}` `{year}`

Placeholders natifs **mPDF** (résolus page par page, à laisser tels quels) :

`{PAGENO}` (n° de page courant) · `{nbpg}` (nombre total de pages) ·
`{DATE j-m-Y}` (date du jour)

Exemple :

```html
<div style="border-top:1px solid #ccc;padding-top:6px;font-size:8px;color:#777;text-align:center;">
  {company_name} — {company_address} — TVA {company_vat}<br>
  Page {PAGENO} / {nbpg}
</div>
```

## Comment ça marche (technique)

Testé pour **Perfex 3.4.1**. Le module s'accroche au hook fired par
`application/libraries/pdf/App_pdf.php` lors de la construction du PDF :

```php
hooks()->do_action('pdf_construct', ['pdf_instance' => $this, 'type' => $this->type()]);
```

- On récupère l'instance mPDF (`pdf_instance`) et le **type de document**
  (`type` : `invoice`, `estimate`, `proposal`, `credit_note`, …) directement
  depuis le payload du hook, puis on appelle `SetHTMLFooter()`.
- Si le type n'est pas fourni, on le déduit en secours du nom de la classe PDF
  (`Invoice_pdf`, `Estimate_pdf`…).
- Aucun fichier de `application/views` ni `application/libraries` n'est modifié.

## Compatibilité

- **Perfex 3.x (dont 3.4.1)** — moteur mPDF : pied de page injecté
  automatiquement via le hook `pdf_construct`.
- **Anciennes versions sous TCPDF** (pas de `SetHTMLFooter`) : le module se
  désactive proprement (aucune erreur).

## Désinstallation

Désactiver le module dans **Setup → Modules**. Les options restent en base ;
supprimez-les manuellement si besoin (préfixe `pdf_footer_manager_` dans
`tbloptions`).
