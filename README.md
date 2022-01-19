# MarkdownWiki parser

## About
Extension of the [Cebe](https://github.com/cebe)'s [Markdown](https://github.com/cebe/markdown) library for parsing Mrkdown into WikiText syntax.

It's used as backend library for [MediaWiki](https://www.mediawiki.org) [MarkdownWiki](https://github.com/PavelD/mw-markdown-wiki) parser.

## Features
The parser render links and tables in wiki format.

### Supported elements

#### Inline elemets
* bold
* italc
* code highlit
* links
* images

#### Block elements
* blockquote
* headlines
* horizontal ruler
* ordered and unordered lists
* paragraphs
* preformatted text
* tables

### Extra structures
#### Templates
Example:

```
---
template: temaplate name
myparam1: content of my param1
myparam2: content of my param2
---
```
first 2 lines are mandatory. Other lines are optional. Code check if `:` is present.

End of parsing is on the end of the template or on first empty line.

#### Magic words

Double underscored [behavior switches](https://www.mediawiki.org/wiki/Help:Magic_words#Behavior_switches) for MediaWiki parser has the same format as markdown bold text. To avoid issues with that words new format is introduced.

`{{__NOTOC__}}` instread of simple `__NOTOC__`. The markdown-wiki parser is removing curly brackets and magic word is applyed during wiki parsing.


## Installation

The installation is following the reccomandation of the original [Cebe](https://github.com/cebe)'s [Markdown](https://github.com/cebe/markdown) library.

Installation is recommended to be done via [composer](https://getcomposer.org/)) by running:

```
composer require paveld/markdown-wiki "*"
```

and then

```
composer update paveld/markdown-wiki
```

## Configuration

If you don't need to parse some of the elements it's possible to remove parsing by following directive.

``` php
$m = new MarkdownWiki();\
$m->disableParsingRule('headline');
$wikTextb = $m->parse($MarkdownText);
```

### List of possible paramters for the `disableParsingRule()` method:

Some elements are idetified by by several rules.

#### Block elements

* *Code block* by `code` and `fencedcode`
* *Headline* by `headline`
* *Horizontal ruler* by `ahr` and `hr`
* *HTML elements* by `html`
* *Markdown table* by `table`
* *Ordered list* by `ol`
* *Quote block* by `quote`
* *Template* by `aaatemplate` and `template`
* *Unordered list* by `bul` and `ul`

#### Inline elements

* *Escape* by `parseEscape`
* *Greater-than sign* by `parseGt`
* *HTML entities* by `parseEntity`
* *Image* by `parseImage`
* *Inline code* by `parseInlineCode`
* *Links* by `parseLink`
* *Magic word* by `parseMagicWord`
* *Special attributes* by `parseSpecialAttributes`
* *Strong and intalic* by `parseEmphStrong`

## License
MIT
