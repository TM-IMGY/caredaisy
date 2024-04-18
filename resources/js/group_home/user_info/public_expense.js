/**
 * 公費データ。
 * 画面上で取り回すデータの構造を定義する。
 */
export default class PublicExpense{
  /**
   * コンストラクタ。
   * @param {Number} amountBornePerson 本人支払い額。
   * @param {String} bearerNumber 負担者番号。
   * @param {String} confirmationMedicalInsuranceDate 公費情報確認日。
   * @param {String} effectiveStartDate 有効開始日。
   * @param {String} expiryDate 有効終了日。
   * @param {String} legalName 法別番号。
   * @param {Number} publicExpenseInformationId 公費ID。
   * @param {String} recipientNumber 受給者番号。
   */
  constructor(
    amountBornePerson,
    bearerNumber,
    confirmationMedicalInsuranceDate,
    effectiveStartDate,
    expiryDate,
    legalName,
    publicExpenseInformationId,
    recipientNumber
  ){
    this.amountBornePerson = amountBornePerson;
    this.bearerNumber = bearerNumber;
    this.confirmationMedicalInsuranceDate = confirmationMedicalInsuranceDate;
    this.effectiveStartDate = effectiveStartDate;
    this.expiryDate = expiryDate;
    this.legalName = legalName;
    this.publicExpenseInformationId = publicExpenseInformationId;
    this.recipientNumber = recipientNumber;
  }

  /**
   * 本人支払い額を返す。
   * @return {Number}
   */
  getAmountBornePerson(){
    return this.amountBornePerson;
  }

  /**
   * 負担者番号を返す。
   * @return {String}
   */
  getBearerNumber(){
    return this.bearerNumber;
  }

  /**
   * 公費情報確認日を返す。
   * @return {String}
   */
  getConfirmationMedicalInsuranceDate(){
    return this.confirmationMedicalInsuranceDate;
  }

  /**
   * 有効開始日を返す。
   * @return {String}
   */
  getEffectiveStartDate(){
    return this.effectiveStartDate;
  }

  /**
   * 有効終了日を返す。
   * @return {String}
   */
  getExpiryDate(){
    return this.expiryDate;
  }

  /**
   * 法別番号を返す。
   * @return {String}
   */
  getLegalName(){
    return this.legalName;
  }

  /**
   * idを返す。
   * @return {Number}
   */
  getPublicExpenseInformationId(){
    return this.publicExpenseInformationId;
  }

  /**
   * 受給者番号を返す。
   * @return {String}
   */
  getRecipientNumber(){
    return this.recipientNumber;
  }
}
