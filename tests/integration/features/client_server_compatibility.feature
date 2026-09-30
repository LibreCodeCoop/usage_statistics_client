Feature: Usage Statistics Protocol v1 client/server compatibility

  Scenario: the real client submits a report that the server stores and aggregates
    Given as user "admin"
    When sending "post" to ocs "/apps/usage_statistics_server/api/v1/admin/schemas"
      | application   | client_server_e2e |
      | schemaVersion | 1 |
      | metrics       | [{"category":"environment","key":"version","type":"string","kind":"snapshot","aggregation":"distribution","description":"Application version","required":true},{"category":"usage","key":"requests_completed","type":"integer","kind":"period","aggregation":"numerical","description":"Completed requests","required":true}] |
    Then the response should have a status code 201

    When the usage statistics client submits the compatibility report

    Given as user "admin"
    When sending "get" to ocs "/apps/usage_statistics_server/api/v1/admin/applications/client_server_e2e/metrics/environment/version/distribution"
    Then the response should have a status code 200
    And the response should be a JSON array with the following mandatory values
      | key                             | value |
      | (jq).ocs.data.values[0].value   | 1.2.3 |
      | (jq).ocs.data.values[0].count   | 1     |

    When sending "get" to ocs "/apps/usage_statistics_server/api/v1/admin/applications/client_server_e2e/metrics/usage/requests_completed/numerical"
    Then the response should have a status code 200
    And the response should be a JSON array with the following mandatory values
      | key                               | value |
      | (jq).ocs.data.statistics.count    | 1     |
      | (jq).ocs.data.statistics.average  | 17    |
      | (jq).ocs.data.statistics.total    | 17    |
