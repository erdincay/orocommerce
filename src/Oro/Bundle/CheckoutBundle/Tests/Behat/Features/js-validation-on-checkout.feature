@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroPaymentTermBundle:PaymentTermIntegration.yml
@fixture-Checkout.yml
@fixture-InventoryLevel.yml
@fixture-CheckoutWorkflow.yml
Feature: Checkout workflow
  Scenario: Check js validation error
    Given There is EUR currency in the system configuration
    And AmandaRCole@example.org customer user has Buyer role
    And I signed in as AmandaRCole@example.org on the store frontend
    When I open page with shopping list List 1
    And I press "Create Order (Custom)"
    And I click on "Checkout Order Review Notes"
    And I press "Continue"
    Then I should see "This value should not be blank."

  Scenario: Check js validation without error
    Given I open page with shopping list List 1
    And I press "Create Order (Custom)"
    And I click on "Checkout Order Review Notes"
    And I fill "Checkout Order Review Form" with:
      | Notes | Customer test note |
    And I press "Continue"
    Then I should not see "This value should not be blank."
    And I should see "Thank You For Your Purchase!"
