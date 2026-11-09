# TYPO3 Extension `empty_page_mark`

This extension marks pages without content elements in the TYPO3 page tree with a warning indicator.
Especially when starting a new project and creating a page structure can often lead to pages which have been created but still need content to be placed at.

![Example](Resources/Public/Screenshots/example.png)

## Installation

Use `composer require fonda/empty-page-mark` to install the extension.

## Configuration

In *Admin Tools > Settings > Extension Configuration > empty_page_mark*, `ignoredDoktypes` lists the page types that are never marked as empty.
Default: `3,4,6,199,254` (link, shortcut, backend user section, divider, folder).

## Credits

This extension was created by Georg Ringer for Fonda GmbH, Vienna.
