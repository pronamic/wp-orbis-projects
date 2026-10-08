# Orbis Projects

## Custom capabilities

https://github.com/WordPress/WordPress-Coding-Standards/wiki/Customizable-sniff-properties#wordpresswpcapabilities-ensure-recognition-of-custom-capabilities

| Capability                     | Description                            |
| ------------------------------ | -------------------------------------- |
| `read_orbis_project_price`     | Allows user to view project price.     |
| `read_orbis_project_agreement` | Allows user to view project agreement. |
| `read_orbis_project_invoice`   | Allows user to view project invoice.   |

## Templates

The plugin ships a single and an archive project template, modelled after `single-orbis_project.php` and `archive-orbis_project.php` of the Orbis 5 theme. They are used unless the theme has its own `single-orbis_project.php` or `archive-orbis_project.php`.

| Template | Shown on | Content |
|---|---|---|
| `templates/archive-orbis_project.php` | Projects archive | Table with client, project, price, time and actions |
| `templates/single-orbis_project.php` | Single project | Layout with description, sections, comments, status, details and involved persons |
| `templates/project-sections.php` | Single project | Tabs from the `orbis_project_sections` filter, the active tab is taken from the `tabs` rewrite endpoint |
| `templates/project-persons.php` | Single project | Persons connected via `orbis_projects_to_persons` |
| `templates/search-form-advanced.php` | Projects archive | Advanced search on client and invoice number, through the `get_template_part_templates/filter_advanced` action of the theme search form |
| `templates/organization-projects.php` | Single organization | Projects of the organization, as a tab through the `orbis_organization_sections` filter |

Other plugins can add content to the single template with the `orbis_before_main_content`, `orbis_after_main_content`, `orbis_before_side_content` and `orbis_after_side_content` actions.
