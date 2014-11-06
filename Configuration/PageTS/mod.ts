mod {
  wizards.newContentElement.wizardItems.forms.elements.hairu_auth {
    icon = ../typo3conf/ext/hairu/Resources/Public/Icons/plugin.png
    title = LLL:EXT:hairu/Resources/Private/Language/locallang_db.xlf:plugin.auth
    description = LLL:EXT:hairu/Resources/Private/Language/locallang_db.xlf:plugin.auth.description

    tt_content_defValues {
      CType = list
      list_type = hairu_auth
    }
  }
}
mod.wizards.newContentElement.wizardItems.forms.show := addToList(hairu_auth)
