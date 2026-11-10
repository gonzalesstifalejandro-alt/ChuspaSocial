@javascript @local @local_chuspasocial
Feature: A student comments on a publication
  In order to interact with publications
  As a student
  I need to be able to comment on a publication

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | One | student1@example.com |

  Scenario: A student comments on a visible publication
    Given I log in as "student1"
    And I visit "/local/chuspasocial/index.php"
    And I set the field "Message" to "Publication for comments"
    And I press "Publish"
    And I should see "Publication for comments"
    When I set the field "Comment" to "This is my comment"
    And I press "Comment"
    Then I should see "This is my comment"
