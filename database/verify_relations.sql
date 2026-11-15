USE university_complaints_db;

-- 1) Show actual foreign-key constraints between the 3 tables.
SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'university_complaints_db'
  AND REFERENCED_TABLE_NAME IS NOT NULL;

-- 2) Each complaint and the student who submitted it (INNER JOIN).
SELECT c.id AS complaint_id, c.title, c.status, u.name AS student
FROM complaints c
JOIN users u ON c.user_id = u.id
ORDER BY c.id DESC;

-- 3) Each administrator reply, and which complaint and administrator it belongs to.
SELECT r.id AS response_id, c.title AS complaint_title,
       a.name AS admin_name, r.message
FROM responses r
JOIN complaints c ON r.complaint_id = c.id
JOIN users a ON r.admin_id = a.id
ORDER BY r.id DESC;
