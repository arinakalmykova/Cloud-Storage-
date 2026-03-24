Feature: Авторизация пользователей
  Как зарегистрированный пользователь
  Я хочу входить в систему
  Чтобы получить доступ к своему аккаунту

  Background:
    Given я очищаю тестовые данные
    And в системе существует пользователь с email "existing@example.com" и паролем "pass1234"

  @positive
  Scenario: Успешный вход с правильными учетными данными
    When я пытаюсь войти с email "existing@example.com" и паролем "pass1234"
    Then вход должен быть успешным
    And я должен получить действительный токен

  @negative
  Scenario Outline: Вход с неверными непустыми учетными данными
    When я пытаюсь войти с email "<email>" и паролем "<password>"
    Then вход должен завершиться с ошибкой "Invalid credentials"

    Examples:
      | email                  | password    |
      | wrong@example.com      | pass1234    |
      | existing@example.com   | wrongpass   |
      | wrong@example.com      | wrongpass   |

  @negative
  Scenario: Вход с пустым паролем
    When я пытаюсь войти с email "existing@example.com" и паролем ""
    Then вход должен завершиться с ошибкой "Invalid credentials"

  @negative
  Scenario: Вход с пустым email
    When я пытаюсь войти с email "" и паролем "pass1234"
    Then вход должен завершиться с ошибкой "Invalid credentials"