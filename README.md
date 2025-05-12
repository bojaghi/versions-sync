# Version Sync

워드프레스 플러그인 또는 테마에 명시된 버전 정보를 기반으로 다른 파일에 기록된 버전 정보를 동기화합니다. 

## 설치하기

```bash
composer require bojaghi/version-sync --dev
````

## 설정하기

`composer.json` 파일에서 'extra' 항목에서 설정합니다.
만약 설정되어 있지 않더라도 않더라도 기본값인 설정에 따라 동작합니다.

```
{
  ...
  "extra": {
    ...
    "version-sync": { ... }
  }
}
```

아래는 설정의 기본값 예시입니다.

```json
{
  "files": [],
  "constant": ""
}
```

각 항목의 의미는 아래와 같습니다.

- `files`: 추가적으로 검색할 파일의 경로를 입력합니다. 기본적으로 플러그인의 메인 파일과 composer.json, package.json 은 포함됩니다. 
- `constant`: PHP 파일 내에서 선언된 버전 상수의 이름을 지정할 수 있습니다.

예를 들어 이렇게 설정할 수 있습니다.

```json
{
  "files": [
    "./function.php"
  ],
  "constant": "MY_THEME_VERSION"
}
```

가령 테마의 경우, 메인 파일이 CSS 입니다. 그러므로 PHP 상수는 메인 파일에서 정의되지 않습니다.
위와 같이 추가로 파일 경로를 입력해야 합니다. 루트 디렉토리의 상대 경로로 입력 가능합니다

## 사용하기

스크립트는 `vendor/bin/version-sync`에 있습니다. 그러므로 `composer.json`에서 다음처럼 입력하면 편리합니다.

```json
{
  "scripts": {
    "vsync": "vendor/bin/version-sync"
  },
  "scripts-descriptions": {
    "vsync": "Run bojaghi/version-sync"
  }
}
```

그리고 `composer run vsync` 처럼 명령을 내릴 수 있습니다.
이 스크립트는 커맨드라인 전용입니다. 웹 환경에서는 실행되지 않습니다.

실행하면 플러그인 혹은 테마의 메인 파일의 버전 정보를 찾아 동기화합니다.

## 일러두기

JSON 파일의 경우 PHP `json_encode`, `json_decode` 함수를 사용해 편집합니다.
의미에 변화는 없으나 파일의 형태가 기존의 작성된 형태와 약간 달라질 수 있습니다.

PHP 상수 선언은 정규식을 통해 파악합니다. 그러므로 버전 상수 선언 시 복잡한 표현식을 사용하면 검출하지 못합니다.
아래 정도로 단순하게 사용하기 바랍니다.

```
const VER = '1.0.0';
// 또는
define( 'VER', '1.0.0 );
```

버전 문자열 변경 후 공백이나 따옴표 같은 문자는 약간 달라질 수 있습니다.
