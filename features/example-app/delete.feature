@delete @remove
Feature: Delete attached to objects files.

  Scenario Outline: Delete attached to the object file.
    Given I have selected driver <driver>
    And I have got an uploaded file named "{tmp}/some-file"
    When I delete the object with id "{last uploaded object id}"
    Then I should get a success status
    And amount of files in upload path is 0

    Examples:
      | driver         |
      | orm            |
      | orm_embeddable |