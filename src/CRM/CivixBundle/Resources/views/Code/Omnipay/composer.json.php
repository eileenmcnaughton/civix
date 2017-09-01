{
  "name": "<?php echo $vendor ?>/omnipay-<?php echo strtolower($processorName) ?>",
  "type": "library",
  "description": "<?php echo ucfirst($processorName) ?> support for Omnipay payment processing library",
  "keywords": [
    "shell",
    "gateway",
    "merchant",
    "omnipay",
    "pay",
    "payment",
    "purchase",
    "<?php echo strtolower($processorName) ?>"
  ],
  "homepage": "https://github.com/<?php echo $githubUserName ?>/omnipay-<?php echo strtolower($processorName) ?>",
  "license": "MIT",
  "authors": [
    {
      "name": "<?php echo $authorName ?>",
      "email": "<?php echo $authorEmail ?>"
    }
  ],
  "autoload": {
    "psr-4": { "Omnipay\\<?php echo $processorName ?>\\" : "src/" }
  },
  "require": {
    "omnipay/common": "~2.0"
  },
  "require-dev": {
    "omnipay/tests": "~2.0"
  }
}