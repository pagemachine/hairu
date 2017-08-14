mod {
  wizards {
    newContentElement {
      wizardItems {
        forms {
          elements {
            hairu_auth {
              iconIdentifier = hairu-wizard-icon
              title = LLL:EXT:hairu/Resources/Private/Language/locallang_db.xlf:plugin.auth
              description = LLL:EXT:hairu/Resources/Private/Language/locallang_db.xlf:plugin.auth.description

              tt_content_defValues {
                CType = list
                list_type = hairu_auth
              }
            }
          }

          show := addToList(hairu_auth)
        }
      }
    }
  }
}
