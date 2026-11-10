@local @local_chuspasocial
Feature: Report and hide a wall publication
  In order to keep the social wall appropriate
  As a student and a manager
  I need to report publications and allow the manager to hide them

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | student1 | Student   | One      | student1@example.com  |
      | manager1 | Manager   | One      | manager1@example.com  |

    And the following "courses" exist:
      | fullname       | shortname |
      | Social course  | social1   |

    And the following "course enrolments" exist:
      | user     | course  | role           |
      | student1 | social1 | student        |
      | manager1 | social1 | editingteacher |

  Scenario: A student reports a publication and the manager hides it
    Given I log in as "student1"
    And I visit "/local/chuspasocial/index.php"
    And I set the field "Message" to "Publication to be reported"
    And I press "Publish"
    And I should see "Publication to be reported"
    When I click on "Report" "link"
    And I log out

    Given I log in as "manager1"
    And I visit "/local/chuspasocial/moderation.php"
    And I should see "Publication to be reported"
    When I click on "Hide" "link"
    And I log out

    Given I log in as "student1"
    When I visit "/local/chuspasocial/index.php"
    Then I should not see "Publication to be reported"
