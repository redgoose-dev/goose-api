"""
Patterns for routes
"""
class Patterns:
    # global
    fields = r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$'
    sort = r'^(asc|desc)$'
    # file
    file_modules = r'^(article|json|checklist)$'
