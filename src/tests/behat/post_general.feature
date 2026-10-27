@local @local_chuspasocial
Feature: Publish on the general wall
  In order to share something with the community
  As a student
  I need to publish a post on the general wall

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |

  Scenario: A student publishes a post on the general wall
    Given I log in as "student1"
    And I visit "/local/chuspasocial/index.php"
    When I set the field "id_content" to "Hola desde el muro general"
    And I press "id_submitbutton"
    Then I should see "Hola desde el muro general"