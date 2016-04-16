@config @stop-action
Feature: Stop action via config.

  Scenario Outline: Stop action on remove.
    Given I have a file named "{extra mappings path}" and with content:
    """
    Atom\Uploader\Model\Embeddable\FileReference:
      delete_on_remove: false
    ExampleApp\Entity\ORM\UploadableEntity:
      delete_on_remove: false
    dbal_uploadable:
      delete_on_remove: false
    """
    And I have selected driver <driver>
    And I have got an uploaded file named "{tmp}/some-file"
    When I delete the object with id "{last uploaded object id}"
    Then amount of files in upload path is 1

    Examples:
      | driver         |
      | dbal           |
      | orm            |
      | orm_embeddable |

  Scenario Outline: Stop action on remove an old file.
    Given I have a file named "{extra mappings path}" and with content:
    """
    Atom\Uploader\Model\Embeddable\FileReference:
      delete_old_file: false
    ExampleApp\Entity\ORM\UploadableEntity:
      delete_old_file: false
    dbal_uploadable:
      delete_old_file: false
    """
    And I have selected driver <driver>
    And I have got an uploaded file named "{tmp}/some-file"
    And I have a file named "{tmp}/another-file"
    When I update object with id "{last uploaded object id}" to replace the file to the new file "{tmp}/another-file"
    Then The file "{upload path}/{last uploaded filename}" is exist

    Examples:
      | driver         |
      | dbal           |
      | orm            |
      | orm_embeddable |

  Scenario Outline: Stop action on inject an uri and file info.
    Given I have a file named "{extra mappings path}" and with content:
    """
    Atom\Uploader\Model\Embeddable\FileReference:
      inject_uri_on_load: false
      inject_file_info_on_load: false
    ExampleApp\Entity\ORM\UploadableEntity:
      inject_uri_on_load: false
      inject_file_info_on_load: false
    dbal_uploadable:
      inject_uri_on_load: false
      inject_file_info_on_load: false
    """
    And I have selected driver <driver>
    And I have got an uploaded file named "{tmp}/some-file"
    When I get an object with id "{last uploaded object id}"
    Then I should see uri null
    And I should see file info null

    Examples:
      | driver         |
      | dbal           |
      | orm            |
      | orm_embeddable |