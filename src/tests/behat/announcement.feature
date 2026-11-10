@local @local_chuspasocial
Feature: A teacher publishes an official announcement
  In order to highlight important information
  As a teacher
  I need to publish an official announcement

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | One | teacher1@example.com |
      | student1 | Student | One | student1@example.com |

    And the following "courses" exist:
      | fullname | shortname |
      | Social course | social1 |

    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | social1 | editingteacher |
      | student1 | social1 | student |

  Scenario: A teacher publishes an official announcement
    Given I log in as "teacher1"
    And I visit "/local/chuspasocial/index.php"
    And I set the field "Message" to "Official announcement for students"
    And I check "Anuncio oficial"
    And I press "Publish"
    Then I should see "Official announcement for students"

    When I log out
    And I log in as "student1"
    And I visit "/local/chuspasocial/index.php"
    Then I should see "Official announcement for students"
    And I should not see "Anuncio oficial"
