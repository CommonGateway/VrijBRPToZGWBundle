# CloudEvent

A notification object conform ZGW Notification API: https://petstore.swagger.io/?url=https://raw.githubusercontent.com/VNG-Realisatie/notificaties-api/1.0.0/src/openapi.yaml#/notificaties/notificaties_create

![Class Diagram](https://github.com/CommonGateway/VrijBRPToZGWBundle/blob/test-with-mapping/docs/schema/cloudevents.notification.svg)

## Properties

| Property | Type | Description | Required |
|----------|------|-------------|----------|
| specversion | string | The spec version of the cloudEvents standard | No |
| id | string | The id of the event | No |
| source | string | The source of the notification | No |
| type | string | The type of notification | No |
| datacontenttype | string | The content type of the data in the notification. | No |
| data | array | The content of the notification | No |
