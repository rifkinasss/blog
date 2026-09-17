# OpenClaw Publishing API

OpenClaw must use a service-account API token, never a dashboard email and password. Create the `OpenClaw` service account and its token from **Dashboard → Users & Access**. The full token is displayed only at creation or regeneration.

Base URL: `/api/v1`  
Authentication: `Authorization: Bearer nbl_…`

## Least-privilege scopes

The normal publishing workflow needs these scopes:

- `articles:create`
- `articles:read`
- `articles:update`
- `articles:preview`
- `media:upload`
- `review:request`

`articles:schedule` and `articles:publish` are opt-in scopes. They also require the matching policy switch in **Dashboard → Automation**. The service account cannot manage users, settings, service accounts, or tokens through this API.

## Workflow

1. Upload an image with `POST /media` using `image` as the multipart field.
2. Create a draft using `POST /articles`. Service-created articles are always drafts.
3. Update only that service account’s own draft with `PATCH /articles/{id}`.
4. Obtain a temporary signed preview using `GET /articles/{id}/preview`.
5. Submit the article with `POST /articles/{id}/request-review`.
6. A dashboard user approves or requests changes in the article editor.
7. If enabled by both scope and policy, OpenClaw may schedule or publish an approved article.

## Endpoints

| Method | Path | Scope |
| --- | --- | --- |
| `GET` | `/articles` | `articles:read` |
| `POST` | `/articles` | `articles:create` |
| `GET` | `/articles/{id}` | `articles:read` |
| `PATCH` | `/articles/{id}` | `articles:update` |
| `GET` | `/articles/{id}/preview` | `articles:preview` |
| `POST` | `/articles/{id}/request-review` | `review:request` |
| `POST` | `/articles/{id}/schedule` | `articles:schedule` |
| `POST` | `/articles/{id}/publish` | `articles:publish` |
| `POST` | `/media` | `media:upload` |

The API is rate-limited to 60 requests per minute. Responses include only the token-owned service articles; a service cannot access manually created articles or articles from another service account.

## Article payload

`title` and Markdown `content` are required when creating an article. Optional fields are `slug`, `excerpt`, `category_id`, `tag_ids`, `cover_image`, `cover_image_alt`, `meta_title`, and `meta_description`. `cover_image` must be a path returned from the media endpoint, for example `covers/server-rack.webp`.

```json
{
  "title": "Running a small monitoring stack",
  "content": "# Monitoring\n\nNotes from the homelab.",
  "excerpt": "A practical monitoring baseline for a small homelab.",
  "tag_ids": [1, 4]
}
```

Do not send API tokens in article content, metadata, or request logs. Revoking or disabling the service account immediately stops its tokens from authenticating.
