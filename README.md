# Backbone

The semi-internal utilities framework for Rybel LLC. 

__No guarantees of correctness or interoperability is made.__

## Functionality

### Helper.php

`Helper.php` abstracts away the SQL query functionality to ensure that all queries follow the best practices

  - All input is sanitized
  - Logging is performed
  - Errors are handled
  - Removes unnecessary array nesting when `LIMIT 1` is used

### LogHelper.php

`LogHelper.php` abstracts away the logging functionality from the rest of the application to ensure it is consistent across applications

### page.php

Classes to handle the rendering of PHP content while abstracting away headers, footers, success acknowledgement and error presentment.

### AuthHelper
Abstract class to handle authentication of a user

#### SamlAuthHelper
Implementation of `AuthHelper` for SAML. Pick a page (usually `index.php`) that will process all SAML requests and call `processSamlInput()`.

##### SSO
To trigger the login workflow, redirect the user to your processing page with `?sso`.
##### ACS
The SAML server should respond to the login workflow to your processing page with `?acs`.
##### SLO
To trigger the logout workflow, redirect the user to your processing page with `?slo`.
##### SLS
The SAML sever should respond to the logout workflow to your processing page with `?sls`.
##### SMD
The SAML server should use your processing page with `?smd` as the entity ID.