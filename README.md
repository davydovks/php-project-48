# gendiff

[![Actions Status](https://github.com/davydovks/php-project-48/workflows/hexlet-check/badge.svg)](https://github.com/davydovks/php-project-48/actions)
[![Maintainability](https://api.codeclimate.com/v1/badges/00cecdd036295d3f8eb7/maintainability)](https://codeclimate.com/github/davydovks/php-project-48/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/00cecdd036295d3f8eb7/test_coverage)](https://codeclimate.com/github/davydovks/php-project-48/test_coverage)

## About

Gendiff compares two configuration files and shows a difference. It can be used both in terminal and as a library. Acceptable input file formats are JSON and YAML.

## Setup

```bash
$ git clone https://github.com/davydovks/php-project-48.git

$ cd php-package-48

$ make install
```

## Usage

### As a library

#### Description

```php
genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish'): string
```

#### Parameters

- **pathToFile1** - Absolute or relative path to the first file to compare.  
- **pathToFile2** - Absolute or relative path to the second file to compare.  
- **format** - Output format. Possible fromats: stylish, plain, json. Default value is 'stylish'.  

#### Example

```php
<?php

use function Differ\Differ\genDiff;

$diff = genDiff($pathToFile1, $pathToFile2, $format);
print_r($diff);
```

### Usage in CLI

```
Usage:
  gendiff [options] <firstFile> <secondFile>

Options:
  -h, --help                    Show help screen
  -v, --version                 Show version
  -f, --format <fmt>            Report format [default: stylish]
```

### Demos

<details><summary>Comparing two plain JSON files, stylish output</summary>

[![asciicast](https://asciinema.org/a/581932.svg)](https://asciinema.org/a/581932)
</details>
<details><summary>Comparing two plain YAML files, stylish output</summary> 

[![asciicast](https://asciinema.org/a/583851.svg)](https://asciinema.org/a/583851)
</details>
<details><summary>Comparing multilevel JSON and YAML files, stylish output</summary> 

[![asciicast](https://asciinema.org/a/587283.svg)](https://asciinema.org/a/587283)
</details>
<details><summary>Comparing two multilevel JSON files, plain output</summary> 

[![asciicast](https://asciinema.org/a/587351.svg)](https://asciinema.org/a/587351)
</details>
<details><summary>Comparing multilevel JSON and YAML files, all output formats</summary> 

[![asciicast](https://asciinema.org/a/587524.svg)](https://asciinema.org/a/587524)
</details>
