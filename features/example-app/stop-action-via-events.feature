@events @stop-action
Feature: Stop action via events.

  Scenario Outline: Stop action on upload.
    Given I have selected driver <driver>
    And I have a file named "{tmp}/some-filename"
    And I register a subscriber "ExampleApp\Subscriber\StopOnUpload"
    When I upload the file "{tmp}/some-filename"
    Then The file "{tmp}/some-filename" is exist

    Examples:
      | driver         |
      | orm            |
      | orm_embeddable |

  Scenario Outline: Stop action on remove.
    Given I have selected driver <driver>
    And I have got an uploaded file named "{tmp}/some-file"
    And I register a subscriber "ExampleApp\Subscriber\StopOnRemove"
    When I delete the object with id "{last uploaded object id}"
    Then amount of files in upload path is 1

    Examples:
      | driver         |
      | orm            |
      | orm_embeddable |

  Scenario Outline: Stop action on update.
    Given I have selected driver <driver>
    And I have got an uploaded file named "{tmp}/some-file"
    And I have a file named "{tmp}/another-file"
    And I register a subscriber "ExampleApp\Subscriber\StopOnUpdate"
    When I update object with id "{last uploaded object id}" to replace the file to the new file "{tmp}/another-file"
    Then The file "{tmp}/another-file" is exist

    Examples:
      | driver         |
      | orm            |
      | orm_embeddable |

  Scenario Outline: Stop action on remove an old file.
    Given I have selected driver <driver>
    And I have got an uploaded file named "{tmp}/some-file"
    And I have a file named "{tmp}/another-file"
    And I register a subscriber "ExampleApp\Subscriber\StopOnRemoveOldFile"
    When I update object with id "{last uploaded object id}" to replace the file to the new file "{tmp}/another-file"
    Then The file "{upload path}/{last uploaded filename}" is exist

    Examples:
      | driver         |
      | orm            |
      | orm_embeddable |

  Scenario Outline: Stop action on inject an uri and file info.
    Given I have selected driver <driver>
    And I have got an uploaded file named "{tmp}/some-file"
    And I register a subscriber "ExampleApp\Subscriber\StopOnInjectUri"
    And I register a subscriber "ExampleApp\Subscriber\StopOnInjectFileInfo"
    When I get an object with id "{last uploaded object id}"
    Then I should see uri null
    And I should see file info null

    Examples:
      | driver         |
      | orm            |
      | orm_embeddable |