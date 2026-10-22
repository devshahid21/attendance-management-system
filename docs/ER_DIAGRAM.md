# Entity relationship diagram

```mermaid
erDiagram
    USERS ||--o| EMPLOYEES : "has one employee profile"
    DEPARTMENTS ||--o{ EMPLOYEES : "contains"
    EMPLOYEES ||--o{ ATTENDANCES : "has"
    ATTENDANCES ||--o{ ATTENDANCE_CORRECTIONS : "has revisions"
    USERS ||--o{ ATTENDANCE_CORRECTIONS : "admin performs"
    USERS {
        bigint id PK
        string name
        string email UK
        string password
        enum role
    }
    DEPARTMENTS {
        bigint id PK
        string name UK
    }
    EMPLOYEES {
        bigint id PK
        bigint user_id FK_UK
        bigint department_id FK
        string employee_code UK
        boolean active
    }
    ATTENDANCES {
        bigint id PK
        bigint employee_id FK
        date attendance_date
        time check_in_at
        time check_out_at
        enum status
    }
    ATTENDANCE_CORRECTIONS {
        bigint id PK
        bigint attendance_id FK
        bigint admin_id FK
        text reason
        json before
        json after
    }
```

**Constraint:** unique (employee_id, attendance_date) prevents duplicate daily attendance.
**Retention:** employees, users and attendances are not deleted when a person is deactivated.
