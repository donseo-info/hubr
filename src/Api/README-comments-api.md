# Hubr Comments API

Base URL: `https://your-site.com/wp-json/hubr/v1`

## Auth

All endpoints require Bearer token:

```
Authorization: Bearer YOUR_HUBR_API_KEY
```

Key defined in `wp-config.php` as `HUBR_API_KEY`.

---

## Endpoints

### GET /posts

List published posts with comment counts.

**Query params:**

| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `page` | int | 1 | Page number |
| `per_page` | int | 20 | Posts per page (max 100) |
| `no_comments_only` | bool | false | Return only posts with 0 comments |

**Response 200:**
```json
{
  "posts": [
    {
      "id": 42,
      "title": "Post title",
      "excerpt": "Short excerpt...",
      "url": "https://site.com/post-slug/",
      "date": "2026-05-20 10:00:00",
      "comment_count": 0
    }
  ],
  "total": 150,
  "total_pages": 8,
  "page": 1
}
```

---

### GET /posts/{id}

Single post with all approved comments.

**Response 200:**
```json
{
  "id": 42,
  "title": "Post title",
  "content": "<p>Full HTML content...</p>",
  "excerpt": "Short excerpt...",
  "url": "https://site.com/post-slug/",
  "date": "2026-05-20 10:00:00",
  "comment_count": 3,
  "comments": [
    {
      "id": 101,
      "parent_id": 0,
      "author": "John",
      "author_id": 5,
      "content": "Great post!",
      "date": "2026-05-20 11:00:00"
    },
    {
      "id": 102,
      "parent_id": 101,
      "author": "Bot",
      "author_id": 0,
      "content": "Thanks for the feedback!",
      "date": "2026-05-20 11:05:00"
    }
  ]
}
```

**Response 404:**
```json
{ "code": "not_found", "message": "Post not found." }
```

---

### POST /posts/{id}/comment

Add a new comment or reply to an existing one.

**Body (JSON):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `content` | string | yes | Comment text (HTML allowed via `wp_kses_post`) |
| `parent_id` | int | no (def: 0) | `0` = new top-level comment, `>0` = reply to that comment ID |
| `author_name` | string | no (def: `"Bot"`) | Display name |
| `author_email` | string | no | Author email |
| `author_user_id` | int | no (def: 0) | WP user ID — if set, name/email pulled from user profile |

**Response 201:**
```json
{
  "success": true,
  "comment_id": 103
}
```

**Error responses:**

| Code | Status | Reason |
|------|--------|--------|
| `not_found` | 404 | Post doesn't exist or not published |
| `comments_closed` | 403 | Comments disabled on post |
| `invalid_parent` | 400 | `parent_id` not found or belongs to different post |
| `insert_failed` | 500 | DB insert error |

---

## Notes

- Comments posted as **immediately approved** (no moderation queue).
- `author_user_id` takes priority for name/email if WP user exists.
- Same `HUBR_API_KEY` used by the publish endpoint (`POST /hubr/v1/publish`).

---

## Typical AI comment flow

```
1. GET /posts?no_comments_only=true          → find posts without comments
2. GET /posts/{id}                           → fetch post content + existing comments
3. (AI generates comment based on content)
4. POST /posts/{id}/comment                  → post the comment
   - new comment:  { content, author_name, author_user_id }
   - reply:        { content, parent_id, author_name, author_user_id }
```
