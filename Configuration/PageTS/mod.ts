mod {
  wizards.newContentElement.wizardItems.forms.elements.hairu_login {
    icon = ../typo3conf/ext/hairu/Resources/Public/Icons/plugin.png
    title = LLL:EXT:hairu/Resources/Private/Language/locallang_db.xlf:plugin.login
    description = LLL:EXT:hairu/Resources/Private/Language/locallang_db.xlf:plugin.login.description

    tt_content_defValues {
      CType = list
      list_type = hairu_login
    }
  }
}
mod.wizards.newContentElement.wizardItems.forms.show := addToList(hairu_login)
