@log @events
Feature: Write log on events.

  Scenario Outline: Write log on upload.
    Given I have selected driver <driver>
    And I have a file named "{tmp}/some-filename"
    And I register a subscriber "ExampleApp\Subscriber\WriteLogOnEvents"
    When I upload the file "{tmp}/some-filename"
    Then The file "{log}/postUpload.log" is exist

    Examples:
      | driver         |
      | orm            |
      | orm_embeddable |

  Scenario Outline: Write log on remove.
    Given I have selected driver <driver>
    And I have got an uploaded file named "{tmp}/some-file"
    And I register a subscriber "ExampleApp\Subscriber\WriteLogOnEvents"
    When I delete the object with id "{last uploaded object id}"
    Then The file "{log}/postRemove.log" is exist

    Examples:
      | driver         |
      | orm            |
      | orm_embeddable |

  Scenario Outline: Write log on update.
    Given I have selected driver <driver>
    And I have got an uploaded file named "{tmp}/some-file"
    And I have a file named "{tmp}/another-file"
    And I register a subscriber "ExampleApp\Subscriber\WriteLogOnEvents"
    When I update object with id "{last uploaded object id}" to replace the file to the new file "{tmp}/another-file"
    Then The file "{log}/postUpdate.log" is exist

    Examples:
      | driver         |
      | orm            |
      | orm_embeddable |

  Scenario Outline: Write log on remove an old file.
    Given I have selected driver <driver>
    And I have got an uploaded file named "{tmp}/some-file"
    And I have a file named "{tmp}/another-file"
    And I register a subscriber "ExampleApp\Subscriber\WriteLogOnEvents"
    When I update object with id "{last uploaded object id}" to replace the file to the new file "{tmp}/another-file"
    Then The file "{log}/postRemoveOldFile.log" is exist

    Examples:
      | driver         |
      | orm            |
      | orm_embeddable |

  Scenario Outline: Write log on inject an uri and file info.
    Given I have selected driver <driver>
    And I have got an uploaded file named "{tmp}/some-file"
    And I register a subscriber "ExampleApp\Subscriber\WriteLogOnEvents"
    When I get an object with id "{last uploaded object id}"
    Then The file "{log}/postInjectUri.log" is exist
    And The file "{log}/postInjectFileInfo.log" is exist

    Examples:
      | driver         |
      | orm            |
      | orm_embeddable |