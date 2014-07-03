# Phi

# A Static Site Generator

(Require PHP version >= 5.4)

Phi is a static site generator that has terse template syntax and high extensibility.

## Usage 

### 0. Compile .phar file

After download the project, run:

```
php composer.phar update
php compile.php
```

to compile the Phi archive file (phi.phar).

### 1. Create scaffold for your site

```
php phi.phar scaffold <project-dir>
```

### 2. Write articles using your favorite markup languages

All articles reside in ```<project-dir>/articles``` directory (it is configurable in config.yml), Phi requires articles to be named according to the Jekyll format:

```
YEAR-MONTH-DAY-NAME.***
```

### 3. Design the templates

Phi use Twig as its default template engine, and extends it with a few useful filters and tags. Templates reside in ```<project-dir>/templates```:

```html
{% extends 'layout.html' %}
{% block content %}
		<div class="sixteen columns">
			<h1 class="remove-bottom" style="margin-top: 40px">Phi</h1>
			<h5>A Static Site Generator</h5>
			<hr />
		</div>
		<div class="two-thirds column">
			{{ page.content }}
		</div>
		<div class="one-third column">
			<h3>Recent Articles</h3>
			<ul class="square">
				{% for article in site.articles|sort_by_date_desc %}
					<a href="{{ article.url }}">
						<li>
							<strong>{{ article.title }}</strong>&nbsp;&nbsp;
							{{ article.content|excerpt|truncate(50) }}
						</li>
					</a>
				{% endfor %}
			</ul>
		</div>
{% endblock %}
```

### 4. Generate site

When you are ready to publish your site, run the ```generate``` command:

```
php phi.phar generate <project-dir>
```

The generated pages reside in ```<project-dir>/site>``` directory.

### 5. Create plugins

Phi provides you with the abilities to add custom markup language parser and site generator:

```
php phi.phar create-plugin -name textile -type parser <project-dir>
```

Phi will generate a plugin bolerplate for you.