@upload
Feature: Upload files and attach to objects.

  Scenario Outline: Upload file and attach to an object.
    Given I have selected driver <driver>
    And I have a file named "{tmp}/some-filename"
    When I upload the file "{tmp}/some-filename"
    Then I should get a success status
    And amount of files in upload path is 1
    Examples:
      | driver         |
      | orm            |
      | orm_embeddable |