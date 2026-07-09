# Pagination Standard

Use numbered pagination, not infinite scroll and not load-more controls.

For ordered entity lists, prefer keyset movement over offset movement:

```sql
WHERE id < :last_id
ORDER BY id DESC
LIMIT :per_page
```

The UI pattern is:

```text
<< < 1 ... 400 401 402 ... 833 > >>
```

Rules:

- Always include first, previous, current-neighbor, next, and last controls when those pages exist.
- Show the current page with the active button style, without square brackets.
- Always include a display-count selector with approved sizes: `10`, `20`, `25`, `50`, `75`, and `100`.
- Preserve list state when opening an item and returning to management screens: filters, display count, cursor/page state, and scroll position should bring the user close to the row they opened.
- Use indexed ordering columns. The default management order is `id DESC` unless the screen has a documented indexed alternative.
- If direct arbitrary page jumps are required, use cursor/page boundary metadata instead of converting the main list query to offset pagination.
