@update
Feature: Update attached to objects files.

  Scenario Outline: Update attached to the object file.
    Given I have selected driver <driver>
    And I have got an uploaded file named "{tmp}/some-file"
    And I have a file named "{tmp}/another-file"
    When I update object with id "{last uploaded object id}" to replace the file to the new file "{tmp}/another-file"
    Then I should get a success status
    And amount of files in upload path is 1

    Examples:
      | driver         |
      | orm            |
      | orm_embeddable |