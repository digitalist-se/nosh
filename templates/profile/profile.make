api = 2
core = 7.x

{% for project in projects %}
projects[{{ project.name }}][type] = {{ project.type }}
projects[{{ project.name }}][version] = {{ project.version }}
  {% if project.download %}
projects[{{ project.name }}][download][type] = {{ project.download.type }}
projects[{{ project.name }}][download][branch] = {{ project.download.branch }}
  {% endif %}
  {% if project.subdir %}
projects[{{ project.name }}][subdir] = {{ project.subdir }}
  {% endif %}
{% endfor %}
