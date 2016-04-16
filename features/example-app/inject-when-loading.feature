@inject
Feature: Inject when loading.

  Scenario Outline: Inject an URI and file info when loading an object.
    Given I have selected driver <driver>
    And I have got an uploaded file named "{tmp}/некоторый-file.ext"
    When I get an object with id "{last uploaded object id}"
    Then I should see uri "<uri>"
    And I should see file info "<fs prefix>{last uploaded filename}"

    Examples:
      | driver         | fs prefix       | uri                                   |
      | dbal           | {upload path}/  | /uploads/{last uploaded filename}     |
      | orm            | {upload path}/  | /uploads/{last uploaded filename}     |
      | orm_embeddable | embeddableFs:// | /flysystem-uploads/некоторый-file.ext |