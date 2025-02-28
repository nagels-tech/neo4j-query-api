# Contributing to Neo4j QueryAPI he PHP Client

Thank you for your interest in contributing to the Neo4j QueryAPI PHP Client! We welcome all contributions, whether it's bug fixes, feature enhancements, or documentation improvements.

## Getting Started

1. **Fork the Repository**\
     
    
Click the "Fork" button at the top right of the repository page.

2. **Clone Your Fork**

   ```bash
   git clone https://github.com/your-username/Neo4j-Client.git
   cd Neo4j-Client
   ```

3. **Set Up the Environment**
 


   - Ensure you have PHP installed (compatible with PHP < 8.1).
   - Install dependencies using Composer:

   ```bash
    composer install
   ```

- Copy the `phpunit.dist.xml` file to `phpunit.xml` and configure the necessary environment variables like `NEO4J_ADDRESS`, `NEO4J_USERNAME`, `NEO4J_PASSWORD`.




4. **Run Tests**


   - Ensure you have PHP installed (compatible with PHP < 8.1).
   - Install dependencies using Composer:

   ```bash
   composer install
   ```

## Code Guidelines

- Ensure your code is **PSR-12 compliant**.
- Use **Psalm** for static analysis. Run:
  ```bash
  composer psalm
  ```
- Apply **code style fixes** using:
  ```bash
  composer cs:fix
  ```

## Making Changes

1. **Create a New Branch**\
   Use a descriptive branch name:

   ```bash
   git checkout -b fix/issue-123
   ```

2. **Make Your Edits**\
   Ensure all tests pass and code is properly formatted.

3. **Commit Your Changes**\
   Write clear commit messages:

   ```bash
   git commit -m "Fix: Corrected query parsing for ProfiledQueryPlan"
   ```

4. **Push Your Branch**

   ```bash
   git push origin fix/issue-123
   ```

## Submitting a Pull Request

1. Go to your forked repository on GitHub.
2. Click on the "New pull request" button.
3. Select your branch and submit the pull request.
4. Add a clear description of the changes you made.

## Review Process

- All PRs are reviewed by the maintainers.
- Ensure CI tests pass before requesting a review.
- Be open to feedback and make revisions as needed.

## Reporting Issues

If you spot a bug or want to suggest a new feature, please [open an issue](https://github.com/NagelsIT/Neo4j-Client/issues) and provide detailed information.

---

We appreciate your contribution — let’s build something powerful together!

