# JobHarbour - Kontrak OpenAPI v1 (OAS 3.1.0)
# Sumber desain: docs/api-design.md (API Design Document v1, 40 endpoint)
# Lint : npx @redocly/cli lint docs/openapi.yaml
# Mock : npx @stoplight/prism-cli mock docs/openapi.yaml
openapi: 3.1.0
info:
  title: JobHarbour API
  version: 1.0.0
  summary: Kontrak REST API platform rekrutmen JobHarbour (Kelompok 4 UHN Tegal).
  description: |-
    Kontrak OpenAPI v1 untuk JobHarbour, platform rekrutmen terintegrasi berbasis microservice dengan mesin pemadanan keahlian otomatis (Filosofi: Pelabuhan para talenta sebelum berlayar ke dunia kerja). Dokumen ini diturunkan dari API Design Document v1 (`docs/api-design.md`) dan menjadi acuan implementasi.

    **Konvensi**
    - Seluruh response sukses memakai envelope `status`, `message`, `data`, `meta`; collection menambahkan `links`.
    - Seluruh error memakai `status`, `message`, `code`, `meta`; error validasi (422) menambahkan `errors` per field.
    - Pagination offset (`page`, `per_page`) dengan batas maksimum; sort memakai awalan `-` untuk menurun.
    - POST pembuat resource mengembalikan 201 + header `Location`; DELETE mengembalikan 204.
    - Otentikasi JWT melalui `Authorization: Bearer <token>`; endpoint publik ditandai `security: []`.

    **Service**: Auth & Profile `:8001`, Job Catalog `:8002`, Application & Matchmaking `:8003`, Recruitment Pipeline `:8004`, diakses melalui API Gateway `:8000`.
  contact:
    name: Kelompok 4 - Pemrograman Web Service, Universitas Harkat Negeri
  license:
    name: MIT
    identifier: MIT
servers:
- url: http://localhost:8000/api/v1
  description: API Gateway (local development)
tags:
- name: Auth
  description: Registrasi, login, dan logout (Auth & Profile Service :8001)
- name: Profile
  description: Profil dan keahlian milik pengguna yang login (Auth & Profile Service :8001)
- name: Users
  description: Data pengguna untuk recruiter, admin, dan service internal (Auth & Profile Service :8001)
- name: Skills
  description: Katalog keahlian (Auth & Profile Service :8001)
- name: Categories
  description: Kategori bidang pekerjaan (Job Catalog Service :8002)
- name: Jobs
  description: Lowongan pekerjaan (Job Catalog Service :8002)
- name: Job Requirements
  description: Syarat keahlian dan bobot matchmaking (Job Catalog Service :8002)
- name: Applications
  description: Lamaran dan skor matchmaking (Application Service :8003)
- name: Pipelines
  description: Tahap seleksi dan riwayat transisi (Pipeline Service :8004)
- name: Assessments
  description: Hasil assessment kandidat (Pipeline Service :8004)
- name: Interviews
  description: Jadwal wawancara (Pipeline Service :8004)
- name: Offerings
  description: Penawaran kerja (Pipeline Service :8004)
security:
- bearerAuth: []
paths:
  /auth/register:
    post:
      tags:
      - Auth
      summary: Mendaftarkan akun baru
      operationId: register
      description: |-
        Mendaftarkan akun baru

        **Aktor:** Public.
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/RegisterInput'
      responses:
        '201':
          description: Akun berhasil dibuat
          headers:
            Location:
              description: URI resource baru
              schema:
                type: string
                example: /api/v1/users/101
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/UserResponse'
        '409':
          description: Bertentangan dengan state resource saat ini
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Error'
              example:
                status: error
                message: Email sudah terdaftar.
                code: EMAIL_ALREADY_REGISTERED
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
        '422':
          description: Validasi body request gagal
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'
              example:
                status: error
                message: Data yang dikirim tidak valid.
                code: VALIDATION_FAILED
                errors:
                  name:
                  - Field name wajib diisi.
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
      security: []
  /auth/login:
    post:
      tags:
      - Auth
      summary: Login dan menerbitkan JWT
      operationId: login
      description: |-
        Kredensial salah dikembalikan sebagai 401 dengan code `UNAUTHENTICATED`.

        **Aktor:** Public.
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/LoginInput'
            example:
              email: rina.saputri@example.com
              password: rahasia123
      responses:
        '200':
          description: Login berhasil, token JWT dikembalikan
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/AuthTokenResponse'
              example:
                status: success
                message: Login berhasil
                data:
                  access_token: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEwMX0.signature
                  token_type: bearer
                  expires_in: 3600
                  user:
                    id: 101
                    name: Rina Saputri
                    email: rina.saputri@example.com
                    role: applicant
                    created_at: '2026-10-01T08:00:00+07:00'
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
        '401':
          description: Email atau password salah
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Error'
              example:
                status: error
                message: Email atau password salah.
                code: UNAUTHENTICATED
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
        '422':
          description: Validasi body request gagal
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'
              example:
                status: error
                message: Data yang dikirim tidak valid.
                code: VALIDATION_FAILED
                errors:
                  email:
                  - Field email wajib diisi.
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
      security: []
  /auth/logout:
    post:
      tags:
      - Auth
      summary: Mencabut token aktif
      operationId: logout
      description: |-
        Mencabut (blacklist) token yang sedang dipakai.

        **Aktor:** Semua (login).
      responses:
        '204':
          description: Berhasil, tanpa body response
        '401':
          $ref: '#/components/responses/Unauthorized'
  /me:
    get:
      tags:
      - Profile
      summary: Melihat profil sendiri
      operationId: getMe
      description: |-
        Profil pengguna yang login

        **Aktor:** Semua (login).
      responses:
        '200':
          description: Profil pengguna yang login
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/MeResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
    patch:
      tags:
      - Profile
      summary: Mengubah profil sendiri
      operationId: updateMe
      description: |-
        Memperbarui sebagian data profil

        **Aktor:** Semua (login).
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/MeUpdateInput'
            example:
              profile:
                location: Tegal
                phone: '081234567890'
      responses:
        '200':
          description: Profil berhasil diperbarui
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/MeResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '422':
          $ref: '#/components/responses/ValidationError'
  /me/skills:
    get:
      tags:
      - Profile
      summary: Melihat keahlian sendiri
      operationId: listMySkills
      description: |-
        Daftar keahlian milik pengguna

        **Aktor:** Applicant.
      parameters:
      - name: sort
        in: query
        description: Urutan data; awalan '-' berarti menurun. Nilai di luar enum ditolak 422.
        schema:
          type: string
          enum:
          - name
          - -name
          - years_exp
          - -years_exp
          default: name
      - $ref: '#/components/parameters/Page'
      - $ref: '#/components/parameters/PerPage'
      responses:
        '200':
          description: Daftar keahlian milik pengguna
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/UserSkillListResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
    put:
      tags:
      - Profile
      summary: Mengganti seluruh keahlian
      operationId: replaceMySkills
      description: |-
        Mengganti seluruh daftar keahlian sekaligus (penggantian utuh).

        **Aktor:** Applicant.
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/UserSkillsReplaceInput'
            example:
              skills:
              - skill_id: 7
                level: intermediate
                years_exp: 2
              - skill_id: 9
                level: beginner
                years_exp: 1
      responses:
        '200':
          description: Daftar keahlian berhasil diganti
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/UserSkillsResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '422':
          description: Validasi body request gagal
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'
              example:
                status: error
                message: Data yang dikirim tidak valid.
                code: VALIDATION_FAILED
                errors:
                  skills:
                  - Field skills wajib diisi.
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
    post:
      tags:
      - Profile
      summary: Menambah satu keahlian
      operationId: addMySkill
      description: |-
        Menambah satu keahlian

        **Aktor:** Applicant.
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/UserSkillInput'
      responses:
        '201':
          description: Keahlian berhasil ditambahkan
          headers:
            Location:
              description: URI resource baru
              schema:
                type: string
                example: /api/v1/me/skills/7
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/UserSkillResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '409':
          description: Bertentangan dengan state resource saat ini
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Error'
              example:
                status: error
                message: Keahlian ini sudah ada di profil Anda.
                code: SKILL_ALREADY_ADDED
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
        '422':
          description: Validasi body request gagal
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'
              example:
                status: error
                message: Data yang dikirim tidak valid.
                code: VALIDATION_FAILED
                errors:
                  skill_id:
                  - Field skill_id wajib diisi.
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
  /me/skills/{skill_id}:
    delete:
      tags:
      - Profile
      summary: Menghapus satu keahlian
      operationId: removeMySkill
      description: |-
        Menghapus satu keahlian

        **Aktor:** Applicant.
      parameters:
      - $ref: '#/components/parameters/SkillId'
      responses:
        '204':
          description: Berhasil, tanpa body response
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
  /users/{id}:
    get:
      tags:
      - Users
      summary: Melihat profil pengguna lain
      operationId: getUser
      description: |-
        Profil publik seorang pengguna

        **Aktor:** Recruiter, Admin.
      parameters:
      - $ref: '#/components/parameters/Id'
      responses:
        '200':
          description: Profil pengguna
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/UserResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
  /users/{id}/skills:
    get:
      tags:
      - Users
      summary: Melihat keahlian pelamar
      operationId: listUserSkills
      description: |-
        Dipakai recruiter dan Application Service (service token) untuk perhitungan matchmaking.

        **Aktor:** Recruiter, Service internal.
      parameters:
      - $ref: '#/components/parameters/Id'
      - name: sort
        in: query
        description: Urutan data; awalan '-' berarti menurun. Nilai di luar enum ditolak 422.
        schema:
          type: string
          enum:
          - name
          - -name
          - years_exp
          - -years_exp
          default: name
      - $ref: '#/components/parameters/Page'
      - $ref: '#/components/parameters/PerPage'
      responses:
        '200':
          description: Daftar keahlian pelamar
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/UserSkillListResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
  /skills:
    get:
      tags:
      - Skills
      summary: Melihat katalog keahlian
      operationId: listSkills
      description: |-
        Katalog keahlian

        **Aktor:** Public.
      parameters:
      - name: q
        in: query
        description: Pencarian pada nama keahlian
        schema:
          type: string
          maxLength: 80
      - name: category
        in: query
        description: Filter kategori keahlian
        schema:
          type: string
          maxLength: 60
      - name: sort
        in: query
        description: Urutan data; awalan '-' berarti menurun. Nilai di luar enum ditolak 422.
        schema:
          type: string
          enum:
          - name
          - -name
          default: name
      - $ref: '#/components/parameters/Page'
      - name: per_page
        in: query
        description: Jumlah data per halaman (default 50, maksimum 100)
        schema:
          type: integer
          minimum: 1
          maximum: 100
          default: 50
      responses:
        '200':
          description: Katalog keahlian
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SkillListResponse'
        '422':
          $ref: '#/components/responses/ValidationError'
      security: []
  /categories:
    get:
      tags:
      - Categories
      summary: Melihat kategori pekerjaan
      operationId: listCategories
      description: |-
        Daftar kategori bidang pekerjaan

        **Aktor:** Public.
      parameters:
      - name: q
        in: query
        description: Pencarian pada nama kategori
        schema:
          type: string
          maxLength: 80
      - name: sort
        in: query
        description: Urutan data; awalan '-' berarti menurun. Nilai di luar enum ditolak 422.
        schema:
          type: string
          enum:
          - name
          - -name
          default: name
      - $ref: '#/components/parameters/Page'
      - name: per_page
        in: query
        description: Jumlah data per halaman (default 50, maksimum 100)
        schema:
          type: integer
          minimum: 1
          maximum: 100
          default: 50
      responses:
        '200':
          description: Daftar kategori pekerjaan
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/CategoryListResponse'
        '422':
          $ref: '#/components/responses/ValidationError'
      security: []
  /jobs:
    get:
      tags:
      - Jobs
      summary: Melihat daftar lowongan
      operationId: listJobs
      description: |-
        Daftar lowongan dengan filter, search, sort, pagination

        **Aktor:** Public.
      parameters:
      - name: q
        in: query
        description: Pencarian pada title dan description
        schema:
          type: string
          maxLength: 80
      - name: category_id
        in: query
        description: Filter berdasarkan kategori
        schema:
          type: integer
          format: int64
          minimum: 1
      - name: location
        in: query
        description: Filter lokasi penempatan
        schema:
          type: string
          maxLength: 100
      - name: employment_type
        in: query
        description: Filter jenis pekerjaan. Boleh lebih dari satu, dipisah koma.
        style: form
        explode: false
        schema:
          type: array
          items:
            type: string
            enum:
            - full-time
            - part-time
            - contract
            - internship
      - name: salary_min
        in: query
        description: Lowongan dengan salary_max lebih besar atau sama dengan nilai ini
        schema:
          type: integer
          minimum: 0
      - name: status
        in: query
        description: Filter status lowongan
        schema:
          type: string
          enum:
          - open
          - closed
          default: open
      - name: sort
        in: query
        description: Urutan data; awalan '-' berarti menurun. Nilai di luar enum ditolak 422.
        schema:
          type: string
          enum:
          - created_at
          - -created_at
          - salary_min
          - -salary_min
          - salary_max
          - -salary_max
          - title
          - -title
          default: -created_at
      - $ref: '#/components/parameters/Page'
      - $ref: '#/components/parameters/PerPage'
      responses:
        '200':
          description: Daftar lowongan
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/JobListResponse'
              example:
                status: success
                message: Daftar lowongan berhasil diambil
                data:
                - id: 13
                  title: Frontend Engineer
                  company_name: PT Solusi Digital
                  location: Bandung
                  employment_type: full-time
                  salary_min: 8500000
                  status: open
                - id: 14
                  title: QA Engineer
                  company_name: PT Kode Nusantara
                  location: Tegal
                  employment_type: contract
                  salary_min: 6000000
                  status: open
                links:
                  first: http://localhost:8000/api/v1/jobs?page=1&per_page=2
                  last: http://localhost:8000/api/v1/jobs?page=9&per_page=2
                  prev: http://localhost:8000/api/v1/jobs?page=1&per_page=2
                  next: http://localhost:8000/api/v1/jobs?page=3&per_page=2
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
                  current_page: 2
                  per_page: 2
                  from: 3
                  to: 4
                  last_page: 9
                  total: 17
        '422':
          $ref: '#/components/responses/ValidationError'
      security: []
    post:
      tags:
      - Jobs
      summary: Membuat lowongan baru
      operationId: createJob
      description: |-
        Menerbitkan lowongan baru

        **Aktor:** Recruiter.
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/JobInput'
            example:
              category_id: 3
              title: Backend Developer
              company_name: PT Teknologi Maju
              description: Membangun REST API microservices dengan Laravel dan MariaDB.
              location: Jakarta
              employment_type: full-time
              salary_min: 9000000
              salary_max: 15000000
              quota: 3
              requirements:
              - skill_name: PHP
                level: required
              - skill_name: Laravel
                level: required
              - skill_name: MySQL
                level: important
              - skill_name: Redis
                level: important
      responses:
        '201':
          description: Lowongan berhasil dibuat
          headers:
            Location:
              description: URI resource baru
              schema:
                type: string
                example: /api/v1/jobs/12
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/JobResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '422':
          description: Validasi body request gagal
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'
              example:
                status: error
                message: Data yang dikirim tidak valid.
                code: VALIDATION_FAILED
                errors:
                  category_id:
                  - Field category_id wajib diisi.
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
  /jobs/my:
    get:
      tags:
      - Jobs
      summary: Daftar lowongan milik recruiter saat ini
      operationId: listMyJobs
      description: |-
        Menampilkan daftar lowongan yang diposting dan dikelola oleh recruiter yang sedang login, beserta jumlah pelamar per lowongan.

        **Aktor:** Recruiter.
      parameters:
      - $ref: '#/components/parameters/Page'
      - $ref: '#/components/parameters/PerPage'
      responses:
        '200':
          description: Daftar lowongan milik recruiter berhasil diambil
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/JobListResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
  /jobs/{id}:
    get:
      tags:
      - Jobs
      summary: Melihat detail lowongan
      operationId: getJob
      description: |-
        Detail lowongan beserta requirements

        **Aktor:** Public.
      parameters:
      - $ref: '#/components/parameters/Id'
      responses:
        '200':
          description: Detail lowongan beserta requirements
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/JobResponse'
        '404':
          $ref: '#/components/responses/NotFound'
      security: []
    put:
      tags:
      - Jobs
      summary: Mengganti seluruh data lowongan
      operationId: replaceJob
      description: |-
        Penggantian utuh; hanya recruiter pemilik lowongan.

        **Aktor:** Recruiter (pemilik).
      parameters:
      - $ref: '#/components/parameters/Id'
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/JobInput'
            example:
              category_id: 3
              title: Backend Developer
              company_name: PT Teknologi Maju
              description: Membangun REST API microservices dengan Laravel dan MariaDB.
              location: Jakarta
              employment_type: full-time
              salary_min: 9000000
              salary_max: 15000000
              quota: 3
              requirements:
              - skill_name: PHP
                level: required
              - skill_name: Laravel
                level: required
              - skill_name: MySQL
                level: important
              - skill_name: Redis
                level: important
      responses:
        '200':
          description: Lowongan berhasil diganti
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/JobResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
        '422':
          description: Validasi body request gagal
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'
              example:
                status: error
                message: Data yang dikirim tidak valid.
                code: VALIDATION_FAILED
                errors:
                  category_id:
                  - Field category_id wajib diisi.
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
    patch:
      tags:
      - Jobs
      summary: Mengubah sebagian data lowongan
      operationId: updateJob
      description: |-
        Perubahan sebagian, misalnya menutup lowongan dengan `{"status": "closed"}`.

        **Aktor:** Recruiter (pemilik).
      parameters:
      - $ref: '#/components/parameters/Id'
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/JobPatchInput'
            example:
              status: closed
      responses:
        '200':
          description: Lowongan berhasil diperbarui
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/JobResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
        '422':
          $ref: '#/components/responses/ValidationError'
    delete:
      tags:
      - Jobs
      summary: Menghapus lowongan
      operationId: deleteJob
      description: |-
        Ditolak 409 bila lowongan masih memiliki lamaran aktif.

        **Aktor:** Recruiter (pemilik), Admin.
      parameters:
      - $ref: '#/components/parameters/Id'
      responses:
        '204':
          description: Berhasil, tanpa body response
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
        '409':
          description: Bertentangan dengan state resource saat ini
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Error'
              example:
                status: error
                message: Lowongan masih memiliki lamaran aktif.
                code: JOB_HAS_ACTIVE_APPLICATIONS
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
  /jobs/{id}/requirements:
    get:
      tags:
      - Job Requirements
      summary: Melihat syarat keahlian
      operationId: listJobRequirements
      description: |-
        Daftar syarat keahlian lowongan

        **Aktor:** Public, Service internal.
      parameters:
      - $ref: '#/components/parameters/Id'
      - name: level
        in: query
        description: Filter tingkat kepentingan
        schema:
          type: string
          enum:
          - required
          - important
          - nice_to_have
      - name: sort
        in: query
        description: Urutan data; awalan '-' berarti menurun. Nilai di luar enum ditolak 422.
        schema:
          type: string
          enum:
          - weight
          - -weight
          - skill_name
          - -skill_name
          default: -weight
      - $ref: '#/components/parameters/Page'
      - $ref: '#/components/parameters/PerPage'
      responses:
        '200':
          description: Daftar syarat keahlian
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/JobRequirementListResponse'
        '404':
          $ref: '#/components/responses/NotFound'
      security: []
    post:
      tags:
      - Job Requirements
      summary: Menambah syarat keahlian
      operationId: createJobRequirement
      description: |-
        Bobot dihitung otomatis dari level: required=3, important=2, nice_to_have=1.

        **Aktor:** Recruiter (pemilik).
      parameters:
      - $ref: '#/components/parameters/Id'
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/JobRequirementInput'
            example:
              skill_name: Redis
              level: important
      responses:
        '201':
          description: Syarat keahlian berhasil ditambahkan
          headers:
            Location:
              description: URI resource baru
              schema:
                type: string
                example: /api/v1/jobs/12/requirements/4
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/JobRequirementResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
        '409':
          description: Bertentangan dengan state resource saat ini
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Error'
              example:
                status: error
                message: Syarat keahlian ini sudah ada pada lowongan.
                code: REQUIREMENT_ALREADY_EXISTS
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
        '422':
          description: Validasi body request gagal
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'
              example:
                status: error
                message: Data yang dikirim tidak valid.
                code: VALIDATION_FAILED
                errors:
                  skill_name:
                  - Field skill_name wajib diisi.
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
  /jobs/{id}/requirements/{requirement_id}:
    delete:
      tags:
      - Job Requirements
      summary: Menghapus syarat keahlian
      operationId: deleteJobRequirement
      description: |-
        Menghapus satu syarat keahlian

        **Aktor:** Recruiter (pemilik).
      parameters:
      - $ref: '#/components/parameters/Id'
      - $ref: '#/components/parameters/RequirementId'
      responses:
        '204':
          description: Berhasil, tanpa body response
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
  /applications:
    post:
      tags:
      - Applications
      summary: Mengirim lamaran dan hitung skor
      operationId: createApplication
      description: |-
        Skor dihitung otomatis dengan Weighted Jaccard Similarity:

        `Score = Σ bobot skill cocok / Σ bobot skill disyaratkan × 100`

        Kategori: excellent 80-100, good 50-79, under_qualified 0-49. Setelah tersimpan, Application Service membuat pipeline melalui `POST /pipelines`.

        **Aktor:** Applicant.
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/ApplicationInput'
            example:
              job_id: 12
              cover_letter: Saya tertarik dengan posisi Backend Developer.
          multipart/form-data:
            schema:
              type: object
              required:
              - job_id
              properties:
                job_id:
                  type: integer
                  format: int64
                  example: 12
                cover_letter:
                  type: string
                  example: Saya tertarik dengan posisi Backend Developer.
                candidate_skills:
                  type: string
                  description: Array JSON string keahlian pelamar (contoh ["PHP","Laravel","MySQL"])
                cv_file:
                  type: string
                  contentMediaType: application/pdf
                  description: Berkas CV pelamar (PDF, maks. 2 MB)
      responses:
        '201':
          description: Lamaran berhasil dibuat beserta skor kecocokan
          headers:
            Location:
              description: URI resource baru
              schema:
                type: string
                example: /api/v1/applications/45
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ApplicationResponse'
              example:
                status: success
                message: Lamaran berhasil dikirim dan skor kecocokan telah dihitung
                data:
                  id: 45
                  job_id: 12
                  user_id: 101
                  applicant_name: Rina Saputri
                  match_score: 80.0
                  match_category: excellent
                  match_detail:
                    matched_skills:
                    - PHP
                    - Laravel
                    - MySQL
                    missing_skills:
                    - Redis
                    matched_weight: 8
                    total_weight: 10
                  status: submitted
                  pipeline_id: 30
                  applied_at: '2026-10-16T17:45:05+07:00'
                meta:
                  timestamp: '2026-10-16T17:45:05+07:00'
                  version: v1
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
        '409':
          description: Sudah melamar (APPLICATION_ALREADY_EXISTS) atau lowongan ditutup (JOB_CLOSED)
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Error'
              example:
                status: error
                message: Anda sudah pernah melamar lowongan ini.
                code: APPLICATION_ALREADY_EXISTS
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
        '422':
          description: Validasi body request gagal
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'
              example:
                status: error
                message: Data yang dikirim tidak valid.
                code: VALIDATION_FAILED
                errors:
                  job_id:
                  - Field job_id wajib diisi.
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
    get:
      tags:
      - Applications
      summary: Melihat riwayat lamaran sendiri
      operationId: listApplications
      description: |-
        Secara implisit hanya mengembalikan lamaran milik pelamar yang login.

        **Aktor:** Applicant.
      parameters:
      - name: status
        in: query
        description: Filter status lamaran. Boleh lebih dari satu, dipisah koma.
        style: form
        explode: false
        schema:
          type: array
          items:
            type: string
            enum:
            - submitted
            - in_review
            - withdrawn
      - name: sort
        in: query
        description: Urutan data; awalan '-' berarti menurun. Nilai di luar enum ditolak 422.
        schema:
          type: string
          enum:
          - applied_at
          - -applied_at
          - match_score
          - -match_score
          default: -applied_at
      - $ref: '#/components/parameters/Page'
      - $ref: '#/components/parameters/PerPage'
      responses:
        '200':
          description: Riwayat lamaran milik pelamar yang login
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ApplicationListResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '422':
          $ref: '#/components/responses/ValidationError'
  /applications/{id}:
    get:
      tags:
      - Applications
      summary: Melihat detail lamaran dan skor
      operationId: getApplication
      description: |-
        Detail lamaran beserta rincian skor kecocokan

        **Aktor:** Applicant (pemilik), Recruiter.
      parameters:
      - $ref: '#/components/parameters/Id'
      responses:
        '200':
          description: Detail lamaran beserta rincian skor
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ApplicationResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
    patch:
      tags:
      - Applications
      summary: Mengubah status lamaran
      operationId: updateApplication
      description: |-
        Mengubah status lamaran (misal submitted ke in_review)

        **Aktor:** Recruiter (pemilik lowongan).
      parameters:
      - $ref: '#/components/parameters/Id'
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/ApplicationStatusInput'
            example:
              status: in_review
      responses:
        '200':
          description: Status lamaran diperbarui
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ApplicationResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
        '422':
          description: Validasi body request gagal
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'
              example:
                status: error
                message: Data yang dikirim tidak valid.
                code: VALIDATION_FAILED
                errors:
                  status:
                  - Field status wajib diisi.
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
    delete:
      tags:
      - Applications
      summary: Menarik lamaran
      operationId: withdrawApplication
      description: |-
        Soft delete: status diubah menjadi `withdrawn`. Ditolak 409 bila kandidat sudah di tahap interview atau lebih.

        **Aktor:** Applicant (pemilik).
      parameters:
      - $ref: '#/components/parameters/Id'
      responses:
        '204':
          description: Berhasil, tanpa body response
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
        '409':
          description: Bertentangan dengan state resource saat ini
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Error'
              example:
                status: error
                message: Lamaran tidak dapat ditarik setelah tahap interview.
                code: WITHDRAWAL_NOT_ALLOWED
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
  /applications/{id}/cv:
    put:
      tags:
      - Applications
      summary: Mengunggah atau mengganti CV
      operationId: uploadApplicationCv
      description: |-
        Singleton sub-resource: satu lamaran memiliki tepat satu CV.

        **Aktor:** Applicant (pemilik).
      parameters:
      - $ref: '#/components/parameters/Id'
      requestBody:
        required: true
        content:
          multipart/form-data:
            schema:
              type: object
              required:
              - file
              properties:
                file:
                  type: string
                  contentMediaType: application/pdf
                  description: Berkas CV (PDF, maks. 2 MB)
      responses:
        '200':
          description: CV berhasil diunggah atau diganti
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ApplicationResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
        '422':
          description: Validasi body request gagal
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'
              example:
                status: error
                message: Data yang dikirim tidak valid.
                code: VALIDATION_FAILED
                errors:
                  file:
                  - Field file wajib diisi.
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
    get:
      tags:
      - Applications
      summary: Mengunduh CV
      operationId: downloadApplicationCv
      description: |-
        Mengunduh berkas CV

        **Aktor:** Applicant (pemilik), Recruiter.
      parameters:
      - $ref: '#/components/parameters/Id'
      responses:
        '200':
          description: Berkas CV
          content:
            application/pdf:
              schema:
                type: string
                contentMediaType: application/pdf
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
  /jobs/{id}/applications:
    get:
      tags:
      - Applications
      summary: Melihat pelamar satu lowongan
      operationId: listJobApplications
      description: |-
        Pelamar satu lowongan, default urut skor tertinggi

        **Aktor:** Recruiter (pemilik).
      parameters:
      - $ref: '#/components/parameters/Id'
      - name: match_category
        in: query
        description: Filter kategori skor. Boleh lebih dari satu, dipisah koma.
        style: form
        explode: false
        schema:
          type: array
          items:
            type: string
            enum:
            - excellent
            - good
            - under_qualified
      - name: status
        in: query
        description: Filter status lamaran. Boleh lebih dari satu, dipisah koma.
        style: form
        explode: false
        schema:
          type: array
          items:
            type: string
            enum:
            - submitted
            - in_review
            - withdrawn
      - name: score_min
        in: query
        description: Skor minimum
        schema:
          type: number
          minimum: 0
          maximum: 100
      - name: sort
        in: query
        description: Urutan data; awalan '-' berarti menurun. Nilai di luar enum ditolak 422.
        schema:
          type: string
          enum:
          - match_score
          - -match_score
          - applied_at
          - -applied_at
          default: -match_score
      - $ref: '#/components/parameters/Page'
      - name: per_page
        in: query
        description: Jumlah data per halaman (default 10, maksimum 30)
        schema:
          type: integer
          minimum: 1
          maximum: 30
          default: 10
      responses:
        '200':
          description: Pelamar lowongan, default urut skor tertinggi
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ApplicationListResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
        '422':
          $ref: '#/components/responses/ValidationError'
  /pipelines:
    post:
      tags:
      - Pipelines
      summary: Membuat pipeline kandidat
      operationId: createPipeline
      description: |-
        Hanya dipanggil Application Service dengan service token.

        **Aktor:** Service internal.
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/PipelineInput'
            example:
              application_id: 45
      responses:
        '201':
          description: Pipeline dibuat dengan tahap awal screening
          headers:
            Location:
              description: URI resource baru
              schema:
                type: string
                example: /api/v1/pipelines/30
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/PipelineResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '409':
          description: Bertentangan dengan state resource saat ini
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Error'
              example:
                status: error
                message: Pipeline untuk lamaran ini sudah ada.
                code: PIPELINE_ALREADY_EXISTS
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
        '422':
          description: Validasi body request gagal
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'
              example:
                status: error
                message: Data yang dikirim tidak valid.
                code: VALIDATION_FAILED
                errors:
                  application_id:
                  - Field application_id wajib diisi.
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
    get:
      tags:
      - Pipelines
      summary: Melihat kandidat per tahap
      operationId: listPipelines
      description: |-
        Daftar kandidat per tahap (tampilan board)

        **Aktor:** Recruiter.
      parameters:
      - name: stage
        in: query
        description: Filter tahap seleksi (tampilan board)
        schema:
          type: string
          enum:
          - screening
          - assessment
          - interview
          - offering
          - rejected
      - name: job_id
        in: query
        description: Filter lowongan milik recruiter
        schema:
          type: integer
          format: int64
          minimum: 1
      - name: sort
        in: query
        description: Urutan data; awalan '-' berarti menurun. Nilai di luar enum ditolak 422.
        schema:
          type: string
          enum:
          - updated_at
          - -updated_at
          - created_at
          - -created_at
          default: -updated_at
      - $ref: '#/components/parameters/Page'
      - name: per_page
        in: query
        description: Jumlah data per halaman (default 20, maksimum 50)
        schema:
          type: integer
          minimum: 1
          maximum: 50
          default: 20
      responses:
        '200':
          description: Daftar kandidat per tahap
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/PipelineListResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '422':
          $ref: '#/components/responses/ValidationError'
  /pipelines/{id}:
    get:
      tags:
      - Pipelines
      summary: Melihat tahap seleksi
      operationId: getPipeline
      description: |-
        Tahap seleksi kandidat saat ini

        **Aktor:** Applicant (pemilik), Recruiter.
      parameters:
      - $ref: '#/components/parameters/Id'
      responses:
        '200':
          description: Tahap seleksi kandidat saat ini
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/PipelineResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
  /pipelines/{id}/transitions:
    post:
      tags:
      - Pipelines
      summary: Memindahkan tahap seleksi
      operationId: createPipelineTransition
      description: |-
        State machine: screening -> assessment -> interview -> offering; setiap tahap boleh ke rejected. Transisi melompat, mundur, atau dari tahap final ditolak 409.

        **Aktor:** Recruiter.
      parameters:
      - $ref: '#/components/parameters/Id'
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/PipelineTransitionInput'
            example:
              to_stage: assessment
              notes: Lolos screening awal
      responses:
        '201':
          description: Tahap seleksi berhasil dipindahkan
          headers:
            Location:
              description: URI resource baru
              schema:
                type: string
                example: /api/v1/pipelines/30/transitions/88
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/PipelineTransitionResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
        '409':
          description: Bertentangan dengan state resource saat ini
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Error'
              example:
                status: error
                message: Kandidat tidak dapat dipindahkan dari screening langsung ke interview.
                code: INVALID_STAGE_TRANSITION
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
        '422':
          description: Validasi body request gagal
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'
              example:
                status: error
                message: Data yang dikirim tidak valid.
                code: VALIDATION_FAILED
                errors:
                  to_stage:
                  - Field to_stage wajib diisi.
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
    get:
      tags:
      - Pipelines
      summary: Melihat riwayat tahap
      operationId: listPipelineTransitions
      description: |-
        Riwayat perpindahan tahap

        **Aktor:** Applicant (pemilik), Recruiter.
      parameters:
      - $ref: '#/components/parameters/Id'
      - name: sort
        in: query
        description: Urutan data; awalan '-' berarti menurun. Nilai di luar enum ditolak 422.
        schema:
          type: string
          enum:
          - created_at
          - -created_at
          default: created_at
      - $ref: '#/components/parameters/Page'
      - $ref: '#/components/parameters/PerPage'
      responses:
        '200':
          description: Riwayat perpindahan tahap (kronologis)
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/PipelineTransitionListResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
  /pipelines/{id}/assessments:
    post:
      tags:
      - Assessments
      summary: Mencatat hasil assessment
      operationId: createAssessment
      description: |-
        Hanya bila kandidat berada di tahap assessment.

        **Aktor:** Recruiter.
      parameters:
      - $ref: '#/components/parameters/Id'
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/AssessmentInput'
      responses:
        '201':
          description: Hasil assessment berhasil dicatat
          headers:
            Location:
              description: URI resource baru
              schema:
                type: string
                example: /api/v1/pipelines/30/assessments/14
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/AssessmentResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
        '409':
          description: Bertentangan dengan state resource saat ini
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Error'
              example:
                status: error
                message: Kandidat tidak sedang berada di tahap assessment.
                code: STAGE_MISMATCH
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
        '422':
          description: Validasi body request gagal
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'
              example:
                status: error
                message: Data yang dikirim tidak valid.
                code: VALIDATION_FAILED
                errors:
                  type:
                  - Field type wajib diisi.
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
  /pipelines/{id}/interviews:
    post:
      tags:
      - Interviews
      summary: Menjadwalkan wawancara
      operationId: createInterview
      description: |-
        Hanya bila kandidat berada di tahap interview.

        **Aktor:** Recruiter.
      parameters:
      - $ref: '#/components/parameters/Id'
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/InterviewInput'
      responses:
        '201':
          description: Wawancara berhasil dijadwalkan
          headers:
            Location:
              description: URI resource baru
              schema:
                type: string
                example: /api/v1/interviews/7
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/InterviewResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
        '409':
          description: Bertentangan dengan state resource saat ini
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Error'
              example:
                status: error
                message: Kandidat tidak sedang berada di tahap interview.
                code: STAGE_MISMATCH
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
        '422':
          description: Validasi body request gagal
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'
              example:
                status: error
                message: Data yang dikirim tidak valid.
                code: VALIDATION_FAILED
                errors:
                  scheduled_at:
                  - Field scheduled_at wajib diisi.
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
    get:
      tags:
      - Interviews
      summary: Melihat jadwal wawancara
      operationId: listPipelineInterviews
      description: |-
        Daftar jadwal wawancara kandidat

        **Aktor:** Applicant (pemilik), Recruiter.
      parameters:
      - $ref: '#/components/parameters/Id'
      - name: status
        in: query
        description: Filter status wawancara
        schema:
          type: string
          enum:
          - scheduled
          - completed
          - cancelled
      - name: sort
        in: query
        description: Urutan data; awalan '-' berarti menurun. Nilai di luar enum ditolak 422.
        schema:
          type: string
          enum:
          - scheduled_at
          - -scheduled_at
          default: scheduled_at
      - $ref: '#/components/parameters/Page'
      - $ref: '#/components/parameters/PerPage'
      responses:
        '200':
          description: Daftar jadwal wawancara
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/InterviewListResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
  /interviews/{id}:
    patch:
      tags:
      - Interviews
      summary: Mengubah jadwal wawancara
      operationId: updateInterview
      description: |-
        Mengubah jadwal atau status wawancara

        **Aktor:** Recruiter.
      parameters:
      - $ref: '#/components/parameters/Id'
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/InterviewPatchInput'
            example:
              status: completed
      responses:
        '200':
          description: Wawancara berhasil diperbarui
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/InterviewResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
        '422':
          $ref: '#/components/responses/ValidationError'
  /pipelines/{id}/offerings:
    post:
      tags:
      - Offerings
      summary: Mengirim penawaran kerja
      operationId: createOffering
      description: |-
        Hanya bila kandidat berada di tahap offering.

        **Aktor:** Recruiter.
      parameters:
      - $ref: '#/components/parameters/Id'
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/OfferingInput'
      responses:
        '201':
          description: Penawaran kerja berhasil dikirim
          headers:
            Location:
              description: URI resource baru
              schema:
                type: string
                example: /api/v1/pipelines/30/offerings/4
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/OfferingResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '403':
          $ref: '#/components/responses/Forbidden'
        '404':
          $ref: '#/components/responses/NotFound'
        '409':
          description: Bertentangan dengan state resource saat ini
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Error'
              example:
                status: error
                message: Kandidat tidak sedang berada di tahap offering.
                code: STAGE_MISMATCH
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
        '422':
          description: Validasi body request gagal
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'
              example:
                status: error
                message: Data yang dikirim tidak valid.
                code: VALIDATION_FAILED
                errors:
                  salary_offered:
                  - Field salary_offered wajib diisi.
                meta:
                  timestamp: '2026-10-16T17:45:00+07:00'
                  version: v1
components:
  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT
      description: Token JWT dari `POST /auth/login`.
  parameters:
    Id:
      name: id
      in: path
      required: true
      description: ID resource
      schema:
        type: integer
        format: int64
        minimum: 1
      example: 12
    SkillId:
      name: skill_id
      in: path
      required: true
      description: ID keahlian
      schema:
        type: integer
        format: int64
        minimum: 1
      example: 7
    RequirementId:
      name: requirement_id
      in: path
      required: true
      description: ID syarat keahlian
      schema:
        type: integer
        format: int64
        minimum: 1
      example: 1
    Page:
      name: page
      in: query
      description: Nomor halaman
      schema:
        type: integer
        minimum: 1
        default: 1
    PerPage:
      name: per_page
      in: query
      description: Jumlah data per halaman (default 15, maksimum 50)
      schema:
        type: integer
        minimum: 1
        maximum: 50
        default: 15
  responses:
    Unauthorized:
      description: Token tidak ada, tidak valid, atau kedaluwarsa
      content:
        application/json:
          schema:
            $ref: '#/components/schemas/Error'
          example:
            status: error
            message: Token tidak valid atau sudah kedaluwarsa.
            code: UNAUTHENTICATED
            meta:
              timestamp: '2026-10-16T17:45:00+07:00'
              version: v1
    Forbidden:
      description: Role atau kepemilikan resource tidak sesuai
      content:
        application/json:
          schema:
            $ref: '#/components/schemas/Error'
          example:
            status: error
            message: Anda tidak memiliki akses ke resource ini.
            code: FORBIDDEN
            meta:
              timestamp: '2026-10-16T17:45:00+07:00'
              version: v1
    NotFound:
      description: Resource tidak ditemukan
      content:
        application/json:
          schema:
            $ref: '#/components/schemas/Error'
          example:
            status: error
            message: Data tidak ditemukan.
            code: RESOURCE_NOT_FOUND
            meta:
              timestamp: '2026-10-16T17:45:00+07:00'
              version: v1
    ValidationError:
      description: Validasi query parameter gagal
      content:
        application/json:
          schema:
            $ref: '#/components/schemas/ValidationError'
          example:
            status: error
            message: Data yang dikirim tidak valid.
            code: VALIDATION_FAILED
            errors:
              per_page:
              - Nilai per_page maksimal 50.
            meta:
              timestamp: '2026-10-16T17:45:00+07:00'
              version: v1
  schemas:
    SuccessMeta:
      type: object
      description: Metadata standar pada setiap response.
      required:
      - timestamp
      - version
      properties:
        timestamp:
          type: string
          format: date-time
          example: '2026-10-16T17:45:00+07:00'
        version:
          type: string
          example: v1
    PaginationMeta:
      type: object
      description: Metadata response collection terpaginasi.
      required:
      - current_page
      - per_page
      - last_page
      - total
      properties:
        timestamp:
          type: string
          format: date-time
          example: '2026-10-16T17:45:00+07:00'
        version:
          type: string
          example: v1
        current_page:
          type: integer
          example: 1
        per_page:
          type: integer
          example: 15
        from:
          type:
          - integer
          - 'null'
          example: 1
        to:
          type:
          - integer
          - 'null'
          example: 15
        last_page:
          type: integer
          example: 2
        total:
          type: integer
          example: 17
    PaginationLinks:
      type: object
      description: Tautan navigasi halaman.
      properties:
        first:
          type:
          - string
          - 'null'
          format: uri
        last:
          type:
          - string
          - 'null'
          format: uri
        prev:
          type:
          - string
          - 'null'
          format: uri
        next:
          type:
          - string
          - 'null'
          format: uri
    Error:
      type: object
      description: Format error seragam untuk seluruh status gagal.
      required:
      - status
      - message
      - code
      properties:
        status:
          type: string
          enum:
          - error
          example: error
        message:
          type: string
          example: Anda sudah pernah melamar lowongan ini.
        code:
          type: string
          example: APPLICATION_ALREADY_EXISTS
        meta:
          $ref: '#/components/schemas/SuccessMeta'
    ValidationError:
      type: object
      description: Error validasi (422) dengan pesan per field.
      required:
      - status
      - message
      - code
      - errors
      properties:
        status:
          type: string
          enum:
          - error
          example: error
        message:
          type: string
          example: Data yang dikirim tidak valid.
        code:
          type: string
          enum:
          - VALIDATION_FAILED
          example: VALIDATION_FAILED
        errors:
          type: object
          additionalProperties:
            type: array
            items:
              type: string
          example:
            title:
            - Judul lowongan wajib diisi.
        meta:
          $ref: '#/components/schemas/SuccessMeta'
    User:
      type: object
      required:
      - id
      - name
      - email
      - role
      properties:
        id:
          type: integer
          format: int64
          minimum: 1
          example: 101
          readOnly: true
        name:
          type: string
          maxLength: 120
          example: Rina Saputri
        email:
          type: string
          format: email
          example: rina.saputri@example.com
        role:
          type: string
          enum:
          - applicant
          - recruiter
          - admin
          example: applicant
        created_at:
          type: string
          format: date-time
          example: '2026-10-01T08:00:00+07:00'
          readOnly: true
    Profile:
      type: object
      properties:
        phone:
          type:
          - string
          - 'null'
          maxLength: 20
          example: '081234567890'
        bio:
          type:
          - string
          - 'null'
          maxLength: 1000
          example: Backend developer yang berfokus pada Laravel.
        location:
          type:
          - string
          - 'null'
          maxLength: 100
          example: Tegal
        portfolio_url:
          type:
          - string
          - 'null'
          format: uri
          example: https://github.com/rinasaputri
    Me:
      description: Singleton profil pengguna yang login (users + profiles).
      allOf:
      - $ref: '#/components/schemas/User'
      - type: object
        properties:
          profile:
            $ref: '#/components/schemas/Profile'
    MeUpdateInput:
      type: object
      description: Field yang boleh diubah sebagian pada PATCH /me.
      properties:
        name:
          type: string
          maxLength: 120
          example: Rina Saputri
        profile:
          $ref: '#/components/schemas/Profile'
    RegisterInput:
      type: object
      required:
      - name
      - email
      - password
      - role
      properties:
        name:
          type: string
          maxLength: 120
          example: Rina Saputri
        email:
          type: string
          format: email
          example: rina.saputri@example.com
        password:
          type: string
          format: password
          minLength: 8
          writeOnly: true
          example: rahasia123
        role:
          type: string
          enum:
          - applicant
          - recruiter
          example: applicant
    LoginInput:
      type: object
      required:
      - email
      - password
      properties:
        email:
          type: string
          format: email
          example: rina.saputri@example.com
        password:
          type: string
          format: password
          writeOnly: true
          example: rahasia123
    AuthToken:
      type: object
      required:
      - access_token
      - token_type
      - expires_in
      properties:
        access_token:
          type: string
          example: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEwMX0.signature
        token_type:
          type: string
          enum:
          - bearer
          example: bearer
        expires_in:
          type: integer
          description: Masa berlaku token dalam detik
          example: 3600
        user:
          $ref: '#/components/schemas/User'
    Skill:
      type: object
      required:
      - id
      - name
      properties:
        id:
          type: integer
          format: int64
          minimum: 1
          example: 7
          readOnly: true
        name:
          type: string
          maxLength: 60
          example: Laravel
        category:
          type: string
          maxLength: 60
          example: Backend
    UserSkill:
      type: object
      required:
      - skill_id
      - name
      - level
      properties:
        skill_id:
          type: integer
          format: int64
          minimum: 1
          example: 7
        name:
          type: string
          example: Laravel
        level:
          type: string
          enum:
          - beginner
          - intermediate
          - advanced
          example: intermediate
        years_exp:
          type: integer
          minimum: 0
          maximum: 50
          example: 2
    UserSkillInput:
      type: object
      required:
      - skill_id
      - level
      properties:
        skill_id:
          type: integer
          format: int64
          minimum: 1
          example: 7
        level:
          type: string
          enum:
          - beginner
          - intermediate
          - advanced
          example: intermediate
        years_exp:
          type: integer
          minimum: 0
          maximum: 50
          example: 2
    UserSkillsReplaceInput:
      type: object
      required:
      - skills
      properties:
        skills:
          type: array
          maxItems: 50
          items:
            $ref: '#/components/schemas/UserSkillInput'
    Category:
      type: object
      required:
      - id
      - name
      - slug
      properties:
        id:
          type: integer
          format: int64
          minimum: 1
          example: 3
          readOnly: true
        name:
          type: string
          example: Software Engineering
        slug:
          type: string
          pattern: ^[a-z0-9-]+$
          example: software-engineering
    JobRequirement:
      type: object
      required:
      - id
      - skill_name
      - level
      - weight
      properties:
        id:
          type: integer
          format: int64
          minimum: 1
          example: 1
          readOnly: true
        skill_name:
          type: string
          maxLength: 60
          example: PHP
        level:
          type: string
          enum:
          - required
          - important
          - nice_to_have
          example: required
        weight:
          type: integer
          enum:
          - 1
          - 2
          - 3
          description: 'Diturunkan dari level: required=3, important=2, nice_to_have=1'
          example: 3
          readOnly: true
    JobRequirementInput:
      type: object
      required:
      - skill_name
      - level
      properties:
        skill_name:
          type: string
          maxLength: 60
          example: Redis
        level:
          type: string
          enum:
          - required
          - important
          - nice_to_have
          example: important
    SalaryRange:
      type: object
      required:
      - min
      - max
      - currency
      properties:
        min:
          type: integer
          minimum: 0
          example: 9000000
        max:
          type: integer
          minimum: 0
          example: 15000000
        currency:
          type: string
          enum:
          - IDR
          example: IDR
    JobSummary:
      type: object
      required:
      - id
      - title
      - company_name
      - status
      properties:
        id:
          type: integer
          format: int64
          minimum: 1
          example: 12
          readOnly: true
        title:
          type: string
          example: Backend Developer
        company_name:
          type: string
          example: PT Teknologi Maju
        location:
          type: string
          example: Jakarta
        employment_type:
          type: string
          enum:
          - full-time
          - part-time
          - contract
          - internship
          example: full-time
        salary_min:
          type: integer
          example: 9000000
        status:
          type: string
          enum:
          - open
          - closed
          example: open
    Job:
      type: object
      required:
      - id
      - title
      - company_name
      - employment_type
      - quota
      - status
      properties:
        id:
          type: integer
          format: int64
          minimum: 1
          example: 12
          readOnly: true
        title:
          type: string
          maxLength: 150
          example: Backend Developer
        category:
          $ref: '#/components/schemas/Category'
        company_name:
          type: string
          maxLength: 150
          example: PT Teknologi Maju
        description:
          type: string
          example: Membangun REST API microservices dengan Laravel dan MariaDB.
        location:
          type: string
          maxLength: 100
          example: Jakarta
        employment_type:
          type: string
          enum:
          - full-time
          - part-time
          - contract
          - internship
          example: full-time
        salary_range:
          $ref: '#/components/schemas/SalaryRange'
        quota:
          type: integer
          minimum: 1
          example: 3
        status:
          type: string
          enum:
          - open
          - closed
          example: open
        requirements:
          type: array
          items:
            $ref: '#/components/schemas/JobRequirement'
        posted_by:
          type: integer
          format: int64
          minimum: 1
          example: 55
          readOnly: true
        created_at:
          type: string
          format: date-time
          example: '2026-10-11T09:00:00+07:00'
          readOnly: true
        updated_at:
          type: string
          format: date-time
          example: '2026-10-11T09:00:00+07:00'
          readOnly: true
    JobInput:
      type: object
      description: Body untuk POST dan PUT /jobs.
      required:
      - category_id
      - title
      - company_name
      - description
      - location
      - employment_type
      - salary_min
      - salary_max
      - quota
      - requirements
      properties:
        category_id:
          type: integer
          format: int64
          minimum: 1
          example: 3
        title:
          type: string
          maxLength: 150
          example: Backend Developer
        company_name:
          type: string
          maxLength: 150
          example: PT Teknologi Maju
        description:
          type: string
          example: Membangun REST API microservices dengan Laravel dan MariaDB.
        location:
          type: string
          maxLength: 100
          example: Jakarta
        employment_type:
          type: string
          enum:
          - full-time
          - part-time
          - contract
          - internship
          example: full-time
        salary_min:
          type: integer
          minimum: 0
          example: 9000000
        salary_max:
          type: integer
          minimum: 0
          example: 15000000
        quota:
          type: integer
          minimum: 1
          example: 3
        status:
          type: string
          enum:
          - open
          - closed
          default: open
          example: open
        requirements:
          type: array
          minItems: 1
          items:
            $ref: '#/components/schemas/JobRequirementInput'
    JobPatchInput:
      type: object
      description: 'Body PATCH /jobs/{id}: kirim hanya field yang diubah, misalnya {"status": "closed"}.'
      properties:
        category_id:
          type: integer
          format: int64
          minimum: 1
          example: 3
        title:
          type: string
          maxLength: 150
          example: Backend Developer
        company_name:
          type: string
          maxLength: 150
          example: PT Teknologi Maju
        description:
          type: string
          example: Membangun REST API microservices dengan Laravel dan MariaDB.
        location:
          type: string
          maxLength: 100
          example: Jakarta
        employment_type:
          type: string
          enum:
          - full-time
          - part-time
          - contract
          - internship
          example: full-time
        salary_min:
          type: integer
          minimum: 0
          example: 9000000
        salary_max:
          type: integer
          minimum: 0
          example: 15000000
        quota:
          type: integer
          minimum: 1
          example: 3
        status:
          type: string
          enum:
          - open
          - closed
          default: open
          example: open
    MatchDetail:
      type: object
      description: Rincian perhitungan Weighted Jaccard Similarity.
      properties:
        matched_skills:
          type: array
          items:
            type: string
          example:
          - PHP
          - Laravel
          - MySQL
        missing_skills:
          type: array
          items:
            type: string
          example:
          - Redis
        matched_weight:
          type: integer
          example: 8
        total_weight:
          type: integer
          example: 10
    Application:
      type: object
      required:
      - id
      - job_id
      - user_id
      - match_score
      - match_category
      - status
      properties:
        id:
          type: integer
          format: int64
          minimum: 1
          example: 45
          readOnly: true
        job_id:
          type: integer
          format: int64
          minimum: 1
          example: 12
        user_id:
          type: integer
          format: int64
          minimum: 1
          example: 101
          readOnly: true
        applicant_name:
          type: string
          example: Rina Saputri
          readOnly: true
        match_score:
          type: number
          minimum: 0
          maximum: 100
          example: 80.0
          readOnly: true
        match_category:
          type: string
          enum:
          - excellent
          - good
          - under_qualified
          example: excellent
          readOnly: true
        match_detail:
          $ref: '#/components/schemas/MatchDetail'
        status:
          type: string
          enum:
          - submitted
          - in_review
          - withdrawn
          example: submitted
        pipeline_id:
          type: integer
          format: int64
          minimum: 1
          example: 30
          readOnly: true
        applied_at:
          type: string
          format: date-time
          example: '2026-10-16T17:45:05+07:00'
          readOnly: true
    ApplicationInput:
      type: object
      required:
      - job_id
      properties:
        job_id:
          type: integer
          format: int64
          minimum: 1
          example: 12
        cover_letter:
          type: string
          maxLength: 3000
          example: Saya tertarik dengan posisi Backend Developer.
    ApplicationStatusInput:
      type: object
      description: Recruiter menandai lamaran sedang ditinjau.
      required:
      - status
      properties:
        status:
          type: string
          enum:
          - in_review
          example: in_review
    Pipeline:
      type: object
      required:
      - id
      - application_id
      - current_stage
      properties:
        id:
          type: integer
          format: int64
          minimum: 1
          example: 30
          readOnly: true
        application_id:
          type: integer
          format: int64
          minimum: 1
          example: 45
        current_stage:
          type: string
          enum:
          - screening
          - assessment
          - interview
          - offering
          - rejected
          example: screening
        created_at:
          type: string
          format: date-time
          example: '2026-10-16T17:45:06+07:00'
          readOnly: true
        updated_at:
          type: string
          format: date-time
          example: '2026-10-18T10:00:00+07:00'
          readOnly: true
    PipelineInput:
      type: object
      required:
      - application_id
      properties:
        application_id:
          type: integer
          format: int64
          minimum: 1
          example: 45
    PipelineTransition:
      type: object
      required:
      - id
      - from_stage
      - to_stage
      properties:
        id:
          type: integer
          format: int64
          minimum: 1
          example: 88
          readOnly: true
        pipeline_id:
          type: integer
          format: int64
          minimum: 1
          example: 30
          readOnly: true
        from_stage:
          type: string
          enum:
          - screening
          - assessment
          - interview
          - offering
          - rejected
          example: screening
        to_stage:
          type: string
          enum:
          - screening
          - assessment
          - interview
          - offering
          - rejected
          example: assessment
        notes:
          type:
          - string
          - 'null'
          example: Lolos screening awal
        changed_by:
          type: integer
          format: int64
          minimum: 1
          example: 55
          readOnly: true
        created_at:
          type: string
          format: date-time
          example: '2026-10-18T10:00:00+07:00'
          readOnly: true
    PipelineTransitionInput:
      type: object
      required:
      - to_stage
      properties:
        to_stage:
          type: string
          enum:
          - assessment
          - interview
          - offering
          - rejected
          example: assessment
        notes:
          type: string
          maxLength: 500
          example: Lolos screening awal
    Assessment:
      type: object
      required:
      - id
      - type
      - score
      properties:
        id:
          type: integer
          format: int64
          minimum: 1
          example: 14
          readOnly: true
        pipeline_id:
          type: integer
          format: int64
          minimum: 1
          example: 30
          readOnly: true
        type:
          type: string
          enum:
          - technical
          - psychological
          example: technical
        score:
          type: number
          minimum: 0
          maximum: 100
          example: 86.5
        notes:
          type:
          - string
          - 'null'
          example: Menguasai Eloquent dan testing
        assessed_at:
          type: string
          format: date-time
          example: '2026-10-20T13:00:00+07:00'
    AssessmentInput:
      type: object
      required:
      - type
      - score
      properties:
        type:
          type: string
          enum:
          - technical
          - psychological
          example: technical
        score:
          type: number
          minimum: 0
          maximum: 100
          example: 86.5
        notes:
          type:
          - string
          - 'null'
          example: Menguasai Eloquent dan testing
        assessed_at:
          type: string
          format: date-time
          example: '2026-10-20T13:00:00+07:00'
    Interview:
      type: object
      required:
      - id
      - scheduled_at
      - type
      - status
      properties:
        id:
          type: integer
          format: int64
          minimum: 1
          example: 7
          readOnly: true
        pipeline_id:
          type: integer
          format: int64
          minimum: 1
          example: 30
          readOnly: true
        scheduled_at:
          type: string
          format: date-time
          example: '2026-10-24T10:00:00+07:00'
        type:
          type: string
          enum:
          - online
          - offline
          example: online
        meeting_url:
          type:
          - string
          - 'null'
          format: uri
          example: https://meet.example.com/paduk-30
        interviewer:
          type: string
          example: Andi Wijaya
        status:
          type: string
          enum:
          - scheduled
          - completed
          - cancelled
          example: scheduled
    InterviewInput:
      type: object
      required:
      - scheduled_at
      - type
      - interviewer
      properties:
        scheduled_at:
          type: string
          format: date-time
          example: '2026-10-24T10:00:00+07:00'
        type:
          type: string
          enum:
          - online
          - offline
          example: online
        meeting_url:
          type:
          - string
          - 'null'
          format: uri
          example: https://meet.example.com/paduk-30
        interviewer:
          type: string
          example: Andi Wijaya
    InterviewPatchInput:
      type: object
      description: Kirim hanya field yang diubah.
      properties:
        scheduled_at:
          type: string
          format: date-time
          example: '2026-10-25T10:00:00+07:00'
        status:
          type: string
          enum:
          - scheduled
          - completed
          - cancelled
          example: completed
    Offering:
      type: object
      required:
      - id
      - salary_offered
      - start_date
      - status
      properties:
        id:
          type: integer
          format: int64
          minimum: 1
          example: 4
          readOnly: true
        pipeline_id:
          type: integer
          format: int64
          minimum: 1
          example: 30
          readOnly: true
        salary_offered:
          type: integer
          minimum: 0
          example: 12000000
        start_date:
          type: string
          format: date
          example: '2026-11-16'
        expires_at:
          type: string
          format: date-time
          example: '2026-11-01T23:59:00+07:00'
        status:
          type: string
          enum:
          - sent
          - accepted
          - declined
          example: sent
    OfferingInput:
      type: object
      required:
      - salary_offered
      - start_date
      - expires_at
      properties:
        salary_offered:
          type: integer
          minimum: 0
          example: 12000000
        start_date:
          type: string
          format: date
          example: '2026-11-16'
        expires_at:
          type: string
          format: date-time
          example: '2026-11-01T23:59:00+07:00'
    UserResponse:
      type: object
      required:
      - status
      - message
      - data
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Profil pengguna berhasil diambil
        data:
          $ref: '#/components/schemas/User'
        meta:
          $ref: '#/components/schemas/SuccessMeta'
    MeResponse:
      type: object
      required:
      - status
      - message
      - data
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Profil berhasil diambil
        data:
          $ref: '#/components/schemas/Me'
        meta:
          $ref: '#/components/schemas/SuccessMeta'
    AuthTokenResponse:
      type: object
      required:
      - status
      - message
      - data
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Login berhasil
        data:
          $ref: '#/components/schemas/AuthToken'
        meta:
          $ref: '#/components/schemas/SuccessMeta'
    JobResponse:
      type: object
      required:
      - status
      - message
      - data
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Detail lowongan berhasil diambil
        data:
          $ref: '#/components/schemas/Job'
        meta:
          $ref: '#/components/schemas/SuccessMeta'
    JobRequirementResponse:
      type: object
      required:
      - status
      - message
      - data
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Syarat keahlian berhasil disimpan
        data:
          $ref: '#/components/schemas/JobRequirement'
        meta:
          $ref: '#/components/schemas/SuccessMeta'
    ApplicationResponse:
      type: object
      required:
      - status
      - message
      - data
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Detail lamaran berhasil diambil
        data:
          $ref: '#/components/schemas/Application'
        meta:
          $ref: '#/components/schemas/SuccessMeta'
    PipelineResponse:
      type: object
      required:
      - status
      - message
      - data
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Tahap seleksi berhasil diambil
        data:
          $ref: '#/components/schemas/Pipeline'
        meta:
          $ref: '#/components/schemas/SuccessMeta'
    PipelineTransitionResponse:
      type: object
      required:
      - status
      - message
      - data
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Tahap seleksi berhasil dipindahkan
        data:
          $ref: '#/components/schemas/PipelineTransition'
        meta:
          $ref: '#/components/schemas/SuccessMeta'
    AssessmentResponse:
      type: object
      required:
      - status
      - message
      - data
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Hasil assessment berhasil dicatat
        data:
          $ref: '#/components/schemas/Assessment'
        meta:
          $ref: '#/components/schemas/SuccessMeta'
    InterviewResponse:
      type: object
      required:
      - status
      - message
      - data
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Wawancara berhasil dijadwalkan
        data:
          $ref: '#/components/schemas/Interview'
        meta:
          $ref: '#/components/schemas/SuccessMeta'
    OfferingResponse:
      type: object
      required:
      - status
      - message
      - data
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Penawaran kerja berhasil dikirim
        data:
          $ref: '#/components/schemas/Offering'
        meta:
          $ref: '#/components/schemas/SuccessMeta'
    UserSkillResponse:
      type: object
      required:
      - status
      - message
      - data
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Keahlian berhasil ditambahkan
        data:
          $ref: '#/components/schemas/UserSkill'
        meta:
          $ref: '#/components/schemas/SuccessMeta'
    UserSkillsResponse:
      type: object
      required:
      - status
      - message
      - data
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Daftar keahlian berhasil disimpan
        data:
          type: array
          items:
            $ref: '#/components/schemas/UserSkill'
        meta:
          $ref: '#/components/schemas/SuccessMeta'
    SkillListResponse:
      type: object
      required:
      - status
      - message
      - data
      - links
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Katalog keahlian berhasil diambil
        data:
          type: array
          items:
            $ref: '#/components/schemas/Skill'
        links:
          $ref: '#/components/schemas/PaginationLinks'
        meta:
          $ref: '#/components/schemas/PaginationMeta'
    CategoryListResponse:
      type: object
      required:
      - status
      - message
      - data
      - links
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Daftar kategori berhasil diambil
        data:
          type: array
          items:
            $ref: '#/components/schemas/Category'
        links:
          $ref: '#/components/schemas/PaginationLinks'
        meta:
          $ref: '#/components/schemas/PaginationMeta'
    JobListResponse:
      type: object
      required:
      - status
      - message
      - data
      - links
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Daftar lowongan berhasil diambil
        data:
          type: array
          items:
            $ref: '#/components/schemas/JobSummary'
        links:
          $ref: '#/components/schemas/PaginationLinks'
        meta:
          $ref: '#/components/schemas/PaginationMeta'
    JobRequirementListResponse:
      type: object
      required:
      - status
      - message
      - data
      - links
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Syarat keahlian berhasil diambil
        data:
          type: array
          items:
            $ref: '#/components/schemas/JobRequirement'
        links:
          $ref: '#/components/schemas/PaginationLinks'
        meta:
          $ref: '#/components/schemas/PaginationMeta'
    UserSkillListResponse:
      type: object
      required:
      - status
      - message
      - data
      - links
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Daftar keahlian berhasil diambil
        data:
          type: array
          items:
            $ref: '#/components/schemas/UserSkill'
        links:
          $ref: '#/components/schemas/PaginationLinks'
        meta:
          $ref: '#/components/schemas/PaginationMeta'
    ApplicationListResponse:
      type: object
      required:
      - status
      - message
      - data
      - links
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Daftar lamaran berhasil diambil
        data:
          type: array
          items:
            $ref: '#/components/schemas/Application'
        links:
          $ref: '#/components/schemas/PaginationLinks'
        meta:
          $ref: '#/components/schemas/PaginationMeta'
    PipelineListResponse:
      type: object
      required:
      - status
      - message
      - data
      - links
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Daftar kandidat berhasil diambil
        data:
          type: array
          items:
            $ref: '#/components/schemas/Pipeline'
        links:
          $ref: '#/components/schemas/PaginationLinks'
        meta:
          $ref: '#/components/schemas/PaginationMeta'
    PipelineTransitionListResponse:
      type: object
      required:
      - status
      - message
      - data
      - links
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Riwayat tahap berhasil diambil
        data:
          type: array
          items:
            $ref: '#/components/schemas/PipelineTransition'
        links:
          $ref: '#/components/schemas/PaginationLinks'
        meta:
          $ref: '#/components/schemas/PaginationMeta'
    InterviewListResponse:
      type: object
      required:
      - status
      - message
      - data
      - links
      - meta
      properties:
        status:
          type: string
          enum:
          - success
          example: success
        message:
          type: string
          example: Jadwal wawancara berhasil diambil
        data:
          type: array
          items:
            $ref: '#/components/schemas/Interview'
        links:
          $ref: '#/components/schemas/PaginationLinks'
        meta:
          $ref: '#/components/schemas/PaginationMeta'
