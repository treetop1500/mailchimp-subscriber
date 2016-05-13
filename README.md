# mailchimp-subscriber
A simple class to be used as a service for subscribing users to mailchimp lists.  Built for use with Symfony projects. This implements the Pacely Mailchimp API 3.0 Wrapper bundle for Symfony.

## Instructions
### 1. Install and Config the Mailchimp Bundle
Install the Pacely Mailchimp bundle in your Symfony project. See: https://github.com/pacely/mailchimp-api-v3

### 2. Add the Subscriber Class
Copy the MailchimpSubscriber.php class into your project where you feel it fits best.  I typically like this sort of stuff in my utility bundle.

### 3. Configuration
Configure your parameters
```#app/config/parameters.yml
parameters:
  ...
  mailchimp_api_key: <your mailchimp api key>
  mailchimp_list_id: <your mailchimp list id>
```

### 4. Create a service
Create your service
```#app/config/services.yml
services:
  ...
  mailchimp.subscriber:
    class: UtilBundle\MailchimpSubscriber
    arguments: ['%mailchimp_api_key%','%mailchimp_list_id%']
```

## Usage

### In A Controller
This example assumes a form is being submitted and there is a 'subscriber' entity.  The statement creates the mailchimp object, subscribes it, and returns a JSON response containing the result.  The result will contain both a 'status' and a 'response'.

```#controller
if ($form->isValid()) {
  $mc = $this->get('mailchimp.subscriber');
  $subscribe_result  = $mc->subscribe($subscriber->getEmail());
  $response = new Response(json_encode($subscribe_result));
  $response->headers->set('Content-Type', 'application/json');
  return $response;
}




