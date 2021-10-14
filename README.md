# Backbone

The internal utilities framework for Rybel LLC. 

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

### page & site

These two classes handle the rendering of PHP content while abstracting away headers, footers, success acknowledgement and error presentment.
