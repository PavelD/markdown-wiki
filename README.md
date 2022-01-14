# MarkdownWiki parser

## About
Extension of the [Cebe](https://github.com/cebe)'s [Markdown](https://github.com/cebe/markdown) library for parsing Mrkdown into WikiText syntax.

Will be used as backed library for future version of [MediaWiki](https://www.mediawiki.org) [Markdown](https://github.com/PavelD/mw-markdown) parser.

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

`{{__NOTOC__}}` instread of simple `__NOTOC__`. The parser is removing curly brackets and magic word is appyed during wiki parsing.


## License
MIT
