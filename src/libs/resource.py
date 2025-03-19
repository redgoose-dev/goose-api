"""
Patterns for routes
"""
class Patterns:
    # global
    fields = r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$'
    sort = r'^(asc|desc)$'
    srls = r'^\d+(,\d+)*$'
    code = r'^[a-zA-Z0-9-_]+$'
    # article
    article_mode = r'^(ready|public|private)$'
    article_duration = r'^(new|old),(regdate|created_at|updated_at),(day|week|month|year)'
    article_random = r'^\d{8}$'
    article_regdate = r'^\d{4}-\d{2}-\d{2}$'
    # category
    category_module = r'^(nest|json)$'
    # comment
    comment_module = r'^(article)$'
    # file
    file_modules = r'^(article|json|checklist|comment)$'
