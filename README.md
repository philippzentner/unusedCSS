# unusedCSS
PHP CLI script to detect unused CSS by walking through all your application files during development.

# Why
There are a lot of tools that removes unused CSS during compilation/deployment.
This tool outputs a txt-file so that you can manually clean up your (S)CSS files to keep files maintainable.

# How
This script does not change anything. Adjust the $PATH variable in the unusedCSS.php file to your frontend-folder/web-application and run it via ```php unusedCSS.php``` and you'll get a file of unused CSS classes within the same folder.
