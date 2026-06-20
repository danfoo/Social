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
- **Pied de page global (HTML)** : appliqué à tous les documents. Édition via un
  **éditeur de texte enrichi** (TinyMCE) avec bouton **code source `</>`** pour
  garder la main sur le HTML/CSS inline. Les champs de fusion sont **cliquables**
  et s'insèrent à la position du curseur. Si TinyMCE n'est pas chargé sur la
  page, l'éditeur retombe automatiquement sur un simple champ texte.
- **Pieds de page par type** : pour chaque type de document (factures, devis,
  propositions, avoirs…), cochez « Utiliser un pied de page personnalisé » et
  saisissez son contenu. **Ce pied de page remplace alors le global pour ce
  type** ; les types non personnalisés continuent d'utiliser le pied de page
  global.
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

Testé pour **Perfex 3.4.1**. Selon l'install, `App_pdf`
(`application/libraries/pdf/App_pdf.php`) étend **TCPDF** *ou* **mPDF**. Le
module gère les deux via les hooks Perfex, sans éditer aucun fichier core :

```php
hooks()->do_action('pdf_construct', ['pdf_instance' => $this, 'type' => $this->type()]);
hooks()->do_action('pdf_footer',    ['pdf_instance' => $this, 'type' => $this->type()]);
```

- **mPDF** : sur `pdf_construct`, on appelle `SetHTMLFooter()` une fois. Les
  jetons `{PAGENO}` / `{nbpg}` sont natifs.
- **TCPDF** : sur `pdf_footer` (appelé dans `Footer()`, à chaque page), on écrit
  le HTML via `writeHTMLCell()` ; `{PAGENO}` / `{nbpg}` sont convertis en alias
  TCPDF (`getAliasNumPage()` / `getAliasNbPages()`).
- Le **type** (`invoice`, `estimate`, `proposal`, `credit_note`, …) vient du
  payload du hook (sinon déduit du nom de classe `Invoice_pdf`, `Estimate_pdf`…).

## Compatibilité

- **Perfex 3.x (dont 3.4.1)** — moteur **TCPDF** (par défaut) **ou mPDF** : pied
  de page injecté automatiquement.
- Si le moteur n'expose ni `SetHTMLFooter` (mPDF) ni `writeHTMLCell` (TCPDF), le
  module se désactive proprement (aucune erreur).

## Désinstallation

Désactiver le module dans **Setup → Modules**. Les options restent en base ;
supprimez-les manuellement si besoin (préfixe `pdf_footer_manager_` dans
`tbloptions`).
