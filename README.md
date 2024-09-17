# Task Management API

## Overview

This Laravel-based Team Project Management System allows users to manage projects, tasks, and user assignments. It supports various functionalities including task filtering, accessing tasks through projects, and managing user contributions with detailed roles and hours.
## Features

- **Project Management**: Create and manage projects.
- **Task Management**: Track tasks with statuses, priorities, and due dates.
- **User Management**: Assign users to projects with specific roles and track their contributions.
- **Task Filtering**: Filter tasks based on status or priority.
- **Latest and Oldest Tasks**: Retrieve the most recent or oldest tasks.
- **High Priority Task Filtering**: Fetch the highest priority task based on specific conditions.


## Project Setup

### Requirements

- PHP >= 8.0
- Composer
- Laravel >= 9.x
- MySQL or another database

### Installation

1. **Clone the Repository**

    ```bash
    git clone https://github.com/Dralve/team-management-api.git
    ```

2. **Navigate to the Project Directory**

    ```bash
    cd team-management-api
    ```

3. **Install Dependencies**

    ```bash
    composer install
    ```

4. **Set Up Environment Variables**

   Copy the `.env.example` file to `.env` and configure your database and other environment settings.

    ```bash
    cp .env.example .env
    ```

   Update the `.env` file with your database credentials and other configuration details.


5. **Run Migrations**

    ```bash
    php artisan migrate
    ```

6. **Seed the Database (To Make Admin)**

    ```bash
    php artisan db:seed
    ```

7. **Start the Development Server**

    ```bash
    php artisan serve
    ```

## API Endpoints

### Authentication

- **Login**: `POST /api/auth/v1/login`
- **Logout**: `POST api/auth/v1/logout`
- **Refresh**: `POST api/auth/v1/refresh`
- **current**: `GET api/auth/v1/current`

### Projects

- **Create Project**: `POST /api/v1/projects`
- **View projects**: `GET /api/v1/projects`
- **Update project**: `PUT /api/v1/projects/{id}`
- **Delete project**: `DELETE /api/v1/projects/{id}`
- **Restore project**: `POST /api/v1/projects/{id}/restore`
- **Get Oldest Task From Project**: `Get /api/v1/projects/{id}/oldest/task`
- **Get Latest Task From Project**: `Get /api/v1/projects/{id}/latest/task`
- **Show Highest Task Priority**: `Get /api/v1/projects/{id}/highest/priority/task`
- **Get Deleted Projects**: `Get /api/v1/get/projects/deleted`
- **Permanently Delete Project**: `Delete /api/v1/projects/{id}/force/delete`

### Tasks

- **Create Task**: `POST /api/v1/projects`
- **View Tasks**: `GET /api/v1/projects`
- **Update Task**: `PUT /api/v1/projects/{id}`
- **Delete Task**: `DELETE /api/v1/projects/{id}`
- **Restore Task**: `POST /api/v1/projects/{id}/restore`
- **Filter Tasks**: `Get /api/v1/user/tasks/filter`
- **Get Highest Priority**: `Get /tasks/highest/priority/{projectId}/{status}'`
- **Show Deleted Task**: `Get /api/v1/get/tasks/deleted`
- **Permanently Delete Task**: `Get /api/v1/tasks/1/force/delete`


### Users

- **Create User**: `POST /api/v1/users`
- **View Users**: `GET /api/v1/users`
- **Update User**: `PUT /api/v1/users/{id}`
- **Delete User**: `DELETE /api/v1/users/{id}`
- **Restore User**: `POST /api/v1/users/{id}/restore`
- **Get User By Id**: `POST /api/v1/users/{id}`
- **Show deleted Users**: `POST /api/v1/get/users/deleted`
- **Show deleted Users**: `POST /api/v1/users/{id}/force/delete`


## Validation Rules

- **TaskFormRequest**: Validates Tasks data including title, description, priority, due_date, status, assigned_to, and created_by.

## Error Handling

Customized error messages and responses are provided to ensure clarity and user-friendly feedback.

## Documentation

All code is documented with appropriate comments and DocBlocks. For more details on the codebase, refer to the inline comments.

## Contributing

Contributions are welcome! Please follow the standard pull request process and adhere to the project's coding standards.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contact

For any questions or issues, please contact [your email] or open an issue on GitHub.

