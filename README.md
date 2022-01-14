# MarkdownWiki parser

## About
Extension of the [Cebe](https://github.com/cebe)'s [Markdown](https://github.com/cebe/markdown) library for parsing Mrkdown into WikiText syntax.

It's used as backend library for [MediaWiki](https://www.mediawiki.org) [MarkdownWiki](https://github.com/PavelD/mw-markdown-wiki) parser.

## Features
The parser render links and tables in wiki format.

### Extra structures
#### Template
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


## License
MIT
