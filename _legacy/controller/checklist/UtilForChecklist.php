<?php
namespace Controller\checklist;

/**
 * util for checklist
 */

class UtilForChecklist {

  /**
   * get percent into checkboxes
   * 내용에 체크박스 갯수를 검사하여 퍼센테이지를 구한다.
   */
  public static function getPercentIntoCheckboxes(string $body): int
  {
    if (!isset($body)) return 0;
    $total = preg_match_all('/\- \[x\]|\- \[ \]/', $body);
    $checked = preg_match_all('/\- \[x\]/', $body);
    if (!($total > 0 && $checked > 0)) return 0;
    return (int)floor($checked / $total * 100);
  }

  /**
   * adjust content
   */
  public static function adjustContent(string $content): string
  {
    return str_replace("'", "\'", $content);
  }

}
